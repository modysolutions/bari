<?php
/**
 * User Area Menu.
 *
 * @package Tamarind_UserArea
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;

/**
 * Add domain to url.
 *
 * @param string $url The url.
 * @return string
 */
function add_domain_to_url( $url ) {
	// if $url contains a domain, return it, else add the domain.
	if ( strpos( $url, 'http' ) !== false ) {
		return $url;
	}
	return get_home_url() . '/' . $url;
}


/**
 * Get the menu link by id.
 *
 * @param string $id The id.
 * @return array
 */
function get_option_menu_link( $id ) {
	if ( get_field( 'url_settings', 'option' ) ) {
		$url_settings = get_field( 'url_settings', 'option' );
		foreach ( $url_settings as $url_setting ) {
			if ( $url_setting['url_key_slug'] === $id ) {
				return array(
					'id'   => $id,
					'name' => $url_setting['url_label'],
					'link' => strtolower( add_domain_to_url( $url_setting['url_slug'] ) ),
				);
			}
		}
	}
	return array(
		'id'   => $id,
		'name' => __( 'No link found', 'tamarind-userarea' ),
		'link' => '#',
	);
}

/**
 * Get usage report link.
 *
 * @return array
 */
function get_usage_report_link() {
	$usage_report_url   = home_url( '/usage-report/' );
	$usage_report_pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-usage-report.php',
		)
	);

	if ( ! empty( $usage_report_pages ) ) {
		$resolved_usage_report_url = get_permalink( (int) $usage_report_pages[0] );
		if ( $resolved_usage_report_url ) {
			$usage_report_url = $resolved_usage_report_url;
		}
	}

	return array(
		'id'   => 'usage_report',
		'name' => __( 'Usage report', 'tamarind-userarea' ),
		'link' => $usage_report_url,
	);
}

/**
 * Build usage report menu section.
 *
 * @return array|null
 */
function get_usage_report_menu_section() {
	if ( ! current_user_can( 'read_tamarind_report' ) ) {
		return null;
	}

	return array(
		'title'   => __( 'Usage report', 'tamarind-userarea' ),
		'options' => array(),
		'islink'  => true,
		'link'    => get_usage_report_link(),
	);
}

/**
 * Retrieve menu structure by user role.
 *
 * @param string $rol User role.
 * @return array Menu structure.
 */
function get_menu_by_rol( $rol ) {
	switch ( $rol ) {
		case 'client':
			$menu = array(
				array(
					'title'   => __( 'Account details', 'tamarind-userarea' ),
					'options' => array(
						get_option_menu_link( 'account_details_my_details' ),
						get_option_menu_link( 'account_details_change_password' ),
					),
				),
				array(
					'title'   => __( 'My subscription', 'tamarind-userarea' ),
					'options' => array(),
					'islink'  => true,
					'link'    => get_option_menu_link( 'my_subscription' ),
				),
				array(
					'title'   => __( 'Communications', 'tamarind-userarea' ),
					'options' => array(
						get_option_menu_link( 'communications_newsletter_preferences' ),
						get_option_menu_link( 'communications_user_contact_preferences' ),
					),
				),
				get_usage_report_menu_section(),
				array(
					'title'   => __( 'Support centre ', 'tamarind-userarea' ),
					'options' => array(),
					'islink'  => true,
					'link'    => get_option_menu_link( 'support_center' ),
				),
			);
			break;

		case 'customer':
		case 'subscriber':
			$menu = array(
				array(
					'title'   => __( 'Account details', 'tamarind-userarea' ),
					'options' => array(
						get_option_menu_link( 'account_details_my_details' ),
						get_option_menu_link( 'account_details_address' ),
						// TODO: Delete this option in future?
						// get_option_menu_link( 'account_details_payment_methods' ),
						get_option_menu_link( 'account_details_change_password' ),
					),
				),
				array(
					'title'   => __( 'My subscription', 'tamarind-userarea' ),
					'options' => array(),
					'islink'  => true,
					'link'    => get_option_menu_link( 'my_subscription' ),
				),
				array(
					'title'   => __( 'Purchased reports', 'tamarind-userarea' ),
					'options' => array(
						get_option_menu_link( 'purchased_reports_orders' ),
						get_option_menu_link( 'purchased_reports_downloads' ),
					),
				),
				get_usage_report_menu_section(),
				array(
					'title'   => __( 'Support centre', 'tamarind-userarea' ),
					'options' => array(),
					'islink'  => true,
					'link'    => get_option_menu_link( 'support_center' ),
				),
			);
			break;

		default:
			/** Dashboard menu for default user role. Hidden for now.
			* array(
			* 'title'   => __( 'Dashboard', 'tamarind-userarea' ),
			* 'options' => array(),
			* 'islink'  => true,
			* 'link'    => get_option_menu_link( 'dashboard' ),
			* ), */
			$menu = array(
				array(
					'title'   => __( 'Account details', 'tamarind-userarea' ),
					'options' => array(
						get_option_menu_link( 'account_details_my_details' ),
						get_option_menu_link( 'account_details_change_password' ),
						get_option_menu_link( 'account_details_address' ),
						// TODO: Delete this option in future?
						// get_option_menu_link( 'account_details_payment_methods' ),
					),
				),
				array(
					'title'   => __( 'My subscription', 'tamarind-userarea' ),
					'options' => array(),
					'islink'  => true,
					'link'    => get_option_menu_link( 'my_subscription' ),
				),
				array(
					'title'   => __( 'Communications', 'tamarind-userarea' ),
					'options' => array(
						get_option_menu_link( 'communications_newsletter_preferences' ),
						get_option_menu_link( 'communications_user_contact_preferences' ),
					),
				),
				array(
					'title'   => __( 'Purchased reports', 'tamarind-userarea' ),
					'options' => array(
						get_option_menu_link( 'purchased_reports_orders' ),
						get_option_menu_link( 'purchased_reports_downloads' ),
					),
				),
				get_usage_report_menu_section(),
				array(
					'title'   => __( 'Support centre', 'tamarind-userarea' ),
					'options' => array(),
					'islink'  => true,
					'link'    => get_option_menu_link( 'support_center' ),
				),
			);
			break;
	}

	return array_values( array_filter( $menu ) );
}

/**
 * Check if a submenu option is active.
 *
 * @param string $param The current parameter from the query.
 * @param array  $option The menu option to check.
 * @return string|false Returns 'active' if the option is active, otherwise false.
 */
function is_active_special_options( $param, $option ) {
	if ( 'purchased-reports-orders-view-order' === $param && 'purchased_reports_orders' === $option['id'] ) {
		return 'active';
	}

	if ( 'account-details-edit-address' === $param && 'account_details_address' === $option['id'] ) {
		return 'active';
	}

	if ( 'account-details-add-payment-methods' === $param && 'account_details_payment_methods' === $option['id'] ) {
		return 'active';
	}

	return false;
}

/**
 * Get icon for user area menu section.
 *
 * @param array $section User area section.
 * @return string
 */
function get_menu_section_icon( array $section ): string {
	$title_slug = 'userarea-' . sanitize_title( $section['title'] );
	$menu_icon  = function_exists( 'tamarind_base\get_svg_icon' ) ? \tamarind_base\get_svg_icon( $title_slug, 'accordion-icon', esc_attr( $section['title'] ) ) : '';

	if ( $menu_icon ) {
		return $menu_icon;
	}

	$section_link_id = $section['link']['id'] ?? '';
	if ( 'usage_report' === $section_link_id ) {
		return '<i class="fa fa-bar-chart accordion-icon" aria-hidden="true"></i>';
	}

	return '';
}

/**
 * Display the User Area Menu dynamically.
 *
 * @param string $rol User role.
 */
function display_userarea_menu( $rol = 'default' ) {
	$selected_menu = get_menu_by_rol( $rol );

	$host         = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$current_slug = 'https://' . $host . $request_uri;
	$param        = get_query_var( 'userarea' ); ?>

	<tm-accordion class="tm-accordion--pills">
		<ul>
			<?php
			foreach ( $selected_menu as $section ) :
				$menu_icon = get_menu_section_icon( $section );
				?>
				<li class="accordion-item">
					<?php if ( isset( $section['islink'] ) ) : ?>
						<a class="menu-title-link" href="<?php echo esc_attr( $section['link']['link'] ); ?>">
							<?php
							if ( $menu_icon ) {
								echo $menu_icon;
							}
							echo esc_html( $section['title'] );
							?>
						</a>
					<?php else : ?>
						<button class="menu-title">
							<?php
							if ( $menu_icon ) {
								echo $menu_icon;
							}
							echo esc_html( $section['title'] );
							?>
						</button>
						<ul class="menu-options">
							<?php
							foreach ( $section['options'] as $option ) :
								if ( is_active_special_options( $param, $option ) ) {
									$is_active = 'active';
								} else {
									// Check if the current URL matches the menu URL.
									// Normalize the URLs for comparison.
									$current_normalized = rtrim( $current_slug, '/' ) . '/';
									$option_normalized  = rtrim( $option['link'], '/' ) . '/';

									// Compare the normalized URLs.
									$is_active = $current_normalized === $option_normalized ? 'active' : '';
								}
								?>
								<li data-id="<?php echo esc_attr( $option['id'] ); ?>" class="<?php echo esc_attr( $is_active ); ?>">
									<a href="<?php echo esc_attr( $option['link'] ); ?>" class="<?php echo esc_attr( $is_active ); ?>">
										<?php echo esc_html( $option['name'] ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
			<li class="accordion-item">
				<a id="tm-userarea-logout-link" class="menu-title" href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">
					<?php
					$logout_icon = function_exists( 'tamarind_base\get_svg_icon' ) ? \tamarind_base\get_svg_icon( 'userarea-logout', 'accordion-icon', esc_attr__( 'Logout', 'tamarind-userarea' ) ) : '';
					echo $logout_icon;
					esc_html_e( 'Logout', 'tamarind-userarea' );
					?>
				</a>
			</li>
		</ul>
	</tm-accordion>
	<?php
}
