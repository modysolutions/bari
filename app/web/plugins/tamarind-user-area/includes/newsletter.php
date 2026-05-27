<?php
/**
 * Newsletter functions
 *
 * @package Tamarind_UserArea
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_userarea;

use function tamarind_subscriptions\access\{current_user_can_read_content_type};

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_tm_newsletter_subscribe', __NAMESPACE__ . '\handle_newsletter_subscribe' );

/**
 * Handle newsletter subscription.
 *
 * @return void
 */
function handle_newsletter_subscribe(): void {
	check_ajax_referer( 'tm_user_area_nonce', 'nonce' );
	
	// TODO groups: delete or change this function in future versions.
	$groups        = sanitize_text_field( $_POST['groups'] );
	$user_settings = sanitize_text_field( $_POST['userSettings'] );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( __( 'No account found', 'tm-user-area' ) );
	}
	
	$user_id = get_current_user_id();
	$user    = get_userdata( $user_id );
	$email   = $user->user_email;
	
	if ( empty( $groups ) ) {
		wp_send_json_error( __( 'Invalid parameters', 'tm-user-area' ) );
	}
	
	$groups = stripslashes( $groups );
	$groups = json_decode( $groups, true );
	
	$user_current_lists  = get_user_subscription_lists( $user_id );
	$subscription_groups = get_subscription_lists();
	sync_user_subscription_lists( $groups, $subscription_groups );
	
	$group_name = key( $groups );
	foreach ( $groups as $group_name => $lists ) {
		$user_current_lists[ $group_name ] = $lists;
		foreach ( $lists as $list ) {
			sync_user_lists_with_zoho_campaigns( $list, $email, 'opt-in' );
		}
		foreach ( $subscription_groups[ $group_name ] as $sg_list ) {
			sync_user_lists_with_zoho_campaigns( $sg_list, $email, 'opt-out' );
		}
	}
	
	$user_settings = stripslashes( $user_settings );
	$user_settings = json_decode( $user_settings, true );
	set_user_current_subscription_lists( $user_id, $user_current_lists );
	set_user_subscription_settings( $user_id, $group_name, $user_settings );
	
	wp_send_json_success(
		array(
			'message' => __( 'Your preferences have been saved.', 'tm-user-area' ),
		)
	);
}

/**
 * @param int $user_id
 *
 * @return array
 */
function get_user_subscription_lists( int $user_id ): array {
	$user_subscriptions = get_user_meta( $user_id, 'tamarind_user_area_subscriptions', true );
	if ( '' === $user_subscriptions ) {
		return get_subscription_lists( false );
	}
	
	return $user_subscriptions;
}

/**
 * Set user current subscription lists.
 *
 * @param int $user_id User ID.
 * @param array $lists Subscription lists.
 *
 * @return void
 */
function set_user_current_subscription_lists( int $user_id, array $lists ): void {
	update_user_meta( $user_id, 'tamarind_user_area_subscriptions', $lists );
}

function get_user_subscription_settings( int $user_id ): array {
	$user_settings = get_user_meta( $user_id, 'tamarind_user_area_subscription_settings', true );
	if ( ! is_array( $user_settings ) ) {
		$user_settings = array();
	}
	
	return $user_settings;
}

/**
 * Set user subscription settings.
 *
 * @param int $user_id User ID.
 * @param string $group_name Group name.
 * @param array $sent_settings Sent user settings.
 *
 * @return void
 */
function set_user_subscription_settings( int $user_id, string $group_name, array $sent_settings ): void {
	if ( ! is_user_logged_in() ) {
		return;
	}
	
	$user_settings = get_user_meta( $user_id, 'tamarind_user_area_subscription_settings', true );
	if ( ! is_array( $user_settings ) ) {
		$user_settings = array();
	}
	
	$user_settings[ $group_name ] = $sent_settings;
	update_user_meta( $user_id, 'tamarind_user_area_subscription_settings', $user_settings );
}

/**
 * @param bool $get_lists
 *
 * @return array
 */
function get_subscription_lists( bool $get_lists = true ): array {
	$subscription_groups = get_field( 'tm_group_newsletter', 'option' ) ?? [];
	$subscription_lists  = array();
	foreach ( $subscription_groups as $group ) {
		$key   = _wp_to_kebab_case( $group['newsletter_group_title'] );
		$lists = array();
		if ( $get_lists ) {
			foreach ( $group['newsletter_group_options'] as $option ) {
				$lists[] = $option['newsletter_group_option_list_id'];
			}
		}
		$subscription_lists[ $key ] = $lists;
	}
	
	return $subscription_lists;
}

/**
 * @param array $new_groups
 * @param array $available_lists
 *
 * @return void
 */
function sync_user_subscription_lists( array &$new_groups, array &$available_lists ): void {
	foreach ( $new_groups as $group_name => $group_lists ) {
		$available_lists[ $group_name ] = array_values( array_diff( $available_lists[ $group_name ], $group_lists ) );
	}
}

/**
 * Set Zoho API list ID.
 *
 * @param string $list_id List ID.
 * @param string $email Email address.
 * @param string $action
 *
 * @return void
 */
function sync_user_lists_with_zoho_campaigns( string $list_id, string $email, string $action ): void {
	if ( empty( $list_id ) || empty( $email ) || empty( $action ) ) {
		return;
	}
	
	if(function_exists('\tamarind_base\send_payload_to_zoho')) {
		$zoho_config = array(
			'payload' => array(
				'email'         => $email,
				'campaign_list' => $list_id,
				'action'        => $action
			),
			'function' => 'opt_user_to_lists',
			'CURLOPT_HTTPHEADER' => array('Content-Type: multipart/form-data')
		);
		\tamarind_base\send_payload_to_zoho($zoho_config);
	}
}

/**
 * Get all data options.
 *
 * @param array $options Options array.
 *
 * @return array
 */
function get_all_data_options( array $options ): array {
	$data = array();
	foreach ( $options as $option ) {
		$option_level_1 = $option['newsletter_group_option_level_1'];
		$option_level_2 = $option['newsletter_group_option_level_2'];
		$option_list_id = $option['newsletter_group_option_list_id'];
		
		$data[] = array(
			'level1'      => $option_level_1,
			'level1_slug' => _wp_to_kebab_case( $option_level_1 ),
			'level2'      => $option_level_2,
			'level2_slug' => _wp_to_kebab_case( $option_level_2 ),
			'list_id'     => $option_list_id,
		);
	}
	
	return $data;
}

/**
 * Display newsletters group options.
 *
 * @param string $group_name Group name.
 * @param array $options Options array.
 *
 * @return void
 */
function display_newsletters_group_options( string $group_name, array $options ): void {
	if ( empty( $options ) ) {
		return;
	}
	
	$block_id = uniqid( 'newsletter_group_options_' . wp_rand( 1, 1000 ) );
	
	$all_data_options           = get_all_data_options( $options );
	$options_level_1            = array_unique( array_filter( array_column( $all_data_options, 'level1' ), fn( $v ) => '' !== $v ) );
	$options_level_2            = array_unique( array_filter( array_column( $all_data_options, 'level2' ), fn( $v ) => '' !== $v ) );
	$user_subscription_settings = get_user_subscription_settings( get_current_user_id() ) ?? array();
	?>
    <div id="<?php echo esc_attr( $block_id ); ?>" class="tm-layout-grid tm-layout-grid--small <?php echo $group_name; ?>"
         data-options="<?php echo esc_attr( wp_json_encode( $all_data_options ) ); ?>">
		<?php if ( ! empty( $options_level_1 ) ) { ?>
            <div class="tm-options-level--one">
				<?php
				foreach ( $options_level_1 as $level1 ) {
                    $user_level1 = $user_subscription_settings[ $group_name ]['level1'] ?? [];
					$checked = is_array($user_level1) && in_array( _wp_to_kebab_case( $level1 ), $user_level1 ) ? 'checked' : '';
					?>
                    <label>
                        <input type="checkbox" class="newsletter-level1"
                               name="level1_<?php echo esc_attr( $block_id ); ?>[]"
                               value="<?php echo _wp_to_kebab_case( $level1 ); ?>" <?php echo esc_attr( $checked ); ?>>
						<?php echo esc_html( $level1 ); ?>
                    </label><br/>
				<?php } ?>
            </div>
		<?php } ?>
		<?php if ( ! empty( $options_level_2 ) ) { ?>
            <div class="tm-options-level--two">
				<?php foreach ( $options_level_2 as $level2 ) {
					$checked = isset( $user_subscription_settings[ $group_name ]['level2'] ) && $user_subscription_settings[ $group_name ]['level2'] === _wp_to_kebab_case( $level2 ) ? 'checked' : '';
					?>
                    <label>
                        <input type="radio" class="newsletter-level2" name="level2_<?php echo esc_attr( $block_id ); ?>"
                               value="<?php echo _wp_to_kebab_case( $level2 ); ?>" <?php echo esc_attr( $checked ); ?>>
						<?php echo esc_html( $level2 ); ?>
                    </label><br/>
				<?php } ?>
            </div>
		<?php } ?>
    </div>
	<?php
}

/**
 * Display newsletters groups.
 *
 * @return void
 */
function display_newsletters_groups(): void {
	$email = '';
	if ( is_user_logged_in() ) {
		$email = get_userdata( get_current_user_id() )->user_email ?? '';
	} else {
		return;
	}
	
	$groups = get_field( 'tm_group_newsletter', 'options' );
	if ( empty( $groups ) ) {
		return;
	}
	
	foreach ( $groups as $group ) {
		$title        = $group['newsletter_group_title'];
		$description  = $group['newsletter_group_description'];
		$image_header = $group['newsletter_group_image'];
        $group_name = _wp_to_kebab_case($title );

        $user_can_read_alerts = false;
        $user_can_read_alerts = current_user_can_read_content_type( 'alerts' );
		
        if ( 'regulatory-alerts' === $group_name && ! $user_can_read_alerts ) {
			continue;
		}

		$block_id = uniqid( 'newsletter_group_' . wp_rand( 1, 1000 ) );
		?>
        <div class="tm-post-card tm-post-card--light tm-post-card--newsletter <?php echo $group_name;?>">
            <section id="<?php echo esc_attr( $block_id ); ?>" class="tm-post-card__inner tm-group-newsletter--card">
				<?php if ( $image_header ) { ?>
                    <div class="tm-post-card__thumbnail tm-group-newsletter--header">
                        <figure class="tm-post-card__image">
                            <img src="<?php echo esc_url( $image_header['url'] ); ?>"
                                 alt="<?php echo esc_attr( $image_header['alt'] ); ?>"/>
                        </figure>
                    </div>
				<?php } ?>
                <div class="tm-post-card__content">
                    <div class="tm-layout-grid" style="--min:300px;--gap:36px;">
                        <div class="tm-post-card__title_excerpt_container">
                            <h3 class="tm-post-card__title">
								<?php echo esc_html( $title ); ?>
                            </h3>
                            <div class="tm-post-card__excerpt">
								<?php echo esc_html( $description ); ?>
                            </div>
                        </div>
                        <div class="tm-post-card__options">
							<?php display_newsletters_group_options( $group_name, $group['newsletter_group_options'] ); ?>
                        </div>
                        <button class="tm-btn btn-default tm-newsletter-subscribe-button"
                                data-group-name="<?php echo _wp_to_kebab_case( esc_attr( $title ) ); ?>"
                                data-group-id="<?php echo esc_attr( $block_id ); ?>" type="button">
							<?php _e( 'Save preferences', 'tamarind-user-area' ); ?>
                        </button>
                        <div class="tm-newsletter-subscribe-message"></div>
                    </div>
            </section>
        </div>
		<?php
	}
	
	return;
}
