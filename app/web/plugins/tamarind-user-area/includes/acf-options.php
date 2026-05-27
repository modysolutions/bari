<?php
/**
 * ACF Fields Options for Tamarind User Area.
 *
 * @package Tamarind_UserArea
 *
 * phpcs:disable WordPress.Files.FileName
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the ACF fields.
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	add_action( 'acf/options_page/save', __NAMESPACE__ . '\flush_rewrite_rules_options_save', 10, 2 );

	add_filter( 'acf/init', __NAMESPACE__ . '\register_acf_fields' );
	add_filter( 'acf/init', __NAMESPACE__ . '\register_url_settings_tab_options' );
	add_filter( 'acf/load_field/key=field_tamarind_userarea_select_dashboard_option', __NAMESPACE__ . '\populate_select_dashboard_option_field' );
	add_filter( 'acf/load_field/key=tm_newsletter_group_option_list_id', __NAMESPACE__ . '\populate_select_subscription_list_field' );
}

/**
 * Flush rewrite rules on the save options page.
 *
 * @param int|string $post_id The post-ID.
 * @param string     $menu_slug The menu slug.
 */
function flush_rewrite_rules_options_save( int|string $post_id, string $menu_slug ): void {
	if ( 'tm-user-area' !== $menu_slug ) {
		return;
	}
	delete_option( 'rewrite_rules' );
}

/**
 * Set the options for the select field.
 *
 * @return array
 */
function set_select_url_options(): array {
	$choices = array(
		'dashboard'                               => 'Dashboard',
		'account_details_my_details'              => 'Account details / My details',
		'account_details_change_password'         => 'Account details / Change password',
		'account_details_address'                 => 'Account details / Address',
		'account_details_edit_address'            => 'Account details / Edit Address',
		'account_details_payment_methods'         => 'Account details / Payment methods',
		'account_details_add_payment_methods'     => 'Account details / Add Payment methods',
		'my_subscription'                         => 'My subscription',
		'communications_newsletter_preferences'   => 'Communications / Newsletter preferences',
		'communications_user_contact_preferences' => 'Communications / User contact preferences',
		'purchased_reports_orders'                => 'Purchased reports / Orders',
		'purchased_reports_orders_view_order'     => 'Purchased reports / View order',
		'purchased_reports_downloads'             => 'Purchased reports / Downloads',
		'support_center'                          => 'Support centre',
		'logout'                                  => 'Logout',
	);

	return array( '' => __( 'Select Key', 'tm-user-area' ) ) + $choices;
}

/**
 * Registers the ACF fields for options Page.
 *
 * @return array
 */
function register_url_repeater_settings(): array {
	$field_url_key_slug = array(
		'key'     => 'tm_key_url',
		'label'   => __( 'Key', 'tm-user-area' ),
		'name'    => 'url_key_slug',
		'type'    => 'select',
		'choices' => set_select_url_options(),
		'wrapper' => array(
			'width' => '33',
		),
	);

	$field_url_slug = array(
		'key'     => 'tm_url_value',
		'label'   => __( 'URL', 'tm-user-area' ),
		'name'    => 'url_slug',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '33',
		),
	);

	$field_url_label = array(
		'key'     => 'tm_url_label',
		'label'   => __( 'Label', 'tm-user-area' ),
		'name'    => 'url_label',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '33',
		),
	);

	return array(
		'key'          => 'tm_url_settings',
		'label'        => __( 'URL', 'tm-user-area' ),
		'name'         => 'url_settings',
		'type'         => 'repeater',
		'parent'       => 'group_tm_url_settings',
		'layout'       => 'block',
		'sub_fields'   => array(
			$field_url_key_slug,
			$field_url_slug,
			$field_url_label,
		),
		'button_label' => 'Añadir Opción',
	);
}

/**
 * Registers the ACF fields for Options Page.
 *
 * @return void
 */
function register_acf_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => 'User Area',
			'menu_title'  => 'User Area',
			'menu_slug'   => 'tm-user-area',
			'capability'  => 'manage_options',
			'parent_slug' => 'tamarind-base',
			'redirect'    => false,
		)
	);

	$dashboard_option = array(
		'key'     => 'field_tamarind_userarea_select_dashboard_option',
		'label'   => __( 'Direct access', 'tamarind-forms' ),
		'name'    => 'select_dashboard_option',
		'type'    => 'select',
		'choices' => array(), // Populate with url-settings to user area.
		'wrapper' => array(
			'width' => '',
		),
	);

	$dashboard_client_repeater = array(
		'key'          => 'tm_dashboard_client',
		'label'        => __( 'Dashboard Client', 'tm-user-area' ),
		'name'         => 'dashboard_client',
		'type'         => 'repeater',
		'layout'       => 'block',
		'sub_fields'   => array(
			$dashboard_option,
		),
		'button_label' => __( 'Add Direct access', 'tm-user-area' ),
		'wrapper'      => array(
			'width' => '50',
		),
	);

	$dashboard_subscriber_repeater = array(
		'key'          => 'tm_dashboard_subscriber',
		'label'        => __( 'Dashboard Subscriber', 'tm-user-area' ),
		'name'         => 'dashboard_subscriber',
		'type'         => 'repeater',
		'layout'       => 'block',
		'sub_fields'   => array(
			$dashboard_option,
		),
		'button_label' => __( 'Add Direct access', 'tm-user-area' ),
		'wrapper'      => array(
			'width' => '50',
		),
	);

	// Register the Contact Information in the Support Centre.
	$support_contact_information = array(
		'key'     => 'tm_contact_information',
		'label'   => __( 'Contact Information', 'tm-user-area' ),
		'name'    => 'contact_information',
		'type'    => 'wysiwyg',
		'wrapper' => array(
			'width' => '50',
		),
	);

	$support_center_link = array(
		'key'           => 'tm_support_center_link',
		'label'         => __( 'Link', 'tm-user-area' ),
		'name'          => 'support_link',
		'type'          => 'link',
		'return_format' => 'array',
	);

	$support_center_links_repeater = array(
		'key'          => 'tm_support_center_links',
		'label'        => __( 'Support Centre Links', 'tm-user-area' ),
		'name'         => 'support_center_links',
		'type'         => 'repeater',
		'layout'       => 'block',
		'sub_fields'   => array(
			$support_center_link,
		),
		'button_label' => __( 'Add Direct access', 'tm-user-area' ),
		'wrapper'      => array(
			'width' => '50',
		),
	);

	// Register the Contact icon link in the Header icons.
	$support_contact_icon_link = array(
		'key'     => 'tm_contact_icon_link',
		'label'   => __( 'Contact icon link', 'tm-user-area' ),
		'name'    => 'contact_icon_link',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '50',
		),
	);

	// Register number of items for Favourites in the Header icons.
	$favourites_number_of_items = array(
		'key'     => 'tm_favourites_number_of_items',
		'label'   => __( 'Number of Items for Favourites', 'tm-user-area' ),
		'name'    => 'favourites_number_of_items',
		'type'    => 'number',
		'wrapper' => array(
			'width' => '25',
		),
	);

	// Register number of items for Notifications in the Header icons.
	$notifications_number_of_items = array(
		'key'     => 'tm_notifications_number_of_items',
		'label'   => __( 'Number of Items for Notifications', 'tm-user-area' ),
		'name'    => 'notifications_number_of_items',
		'type'    => 'number',
		'wrapper' => array(
			'width' => '25',
		),
	);

	// Register the Newsletter  Title in the User Area.
	$newsletter_title = array(
		'key'     => 'tm_newsletter_group_title',
		'label'   => __( 'Group Title', 'tm-user-area' ),
		'name'    => 'newsletter_group_title',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '33',
		),
	);

	// Register the Newsletter Description in the User Area.
	$newsletter_description = array(
		'key'     => 'tm_newsletter_group_description',
		'label'   => __( 'Group Description', 'tm-user-area' ),
		'name'    => 'newsletter_group_description',
		'type'    => 'textarea',
		'rows'    => 5,
		'wrapper' => array(
			'width' => '33',
		),
	);

	// Register the Newsletter Image in the User Area.
	$newsletter_image = array(
		'key'     => 'tm_newsletter_group_image',
		'label'   => __( 'Group Image', 'tm-user-area' ),
		'name'    => 'newsletter_group_image',
		'type'    => 'image',
		'wrapper' => array(
			'width' => '33',
		),
	);

	$newsletter_option_level_1 = array(
		'key'     => 'tm_newsletter_group_option_level_1',
		'label'   => __( 'Level 1', 'tm-user-area' ),
		'name'    => 'newsletter_group_option_level_1',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '33',
		),
	);

	$newsletter_option_level_2 = array(
		'key'     => 'tm_newsletter_group_option_level_2',
		'label'   => __( 'Level 2', 'tm-user-area' ),
		'name'    => 'newsletter_group_option_level_2',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '33',
		),
	);

	$newsletter_option_list_id = array(
		'key'     => 'tm_newsletter_group_option_list_id',
		'label'   => __( 'List ID', 'tm-user-area' ),
		'name'    => 'newsletter_group_option_list_id',
		'type'    => 'select',
		'choices' => array(), // Populate with subscription_list.
		'wrapper' => array(
			'width' => '33',
		),
	);

	// Register the Newsletter Options Layout in the User Area.
	$newsletter_options = array(
		'key'          => 'tm_newsletter_group_options',
		'label'        => __( 'Group Options', 'tm-user-area' ),
		'name'         => 'newsletter_group_options',
		'type'         => 'repeater',
		'layout'       => 'block',
		'sub_fields'   => array(
			$newsletter_option_level_1,
			$newsletter_option_level_2,
			$newsletter_option_list_id,
		),
		'button_label' => 'Add Option',
	);

	// Register the Newsletter Group in the User Area.
	$newsletter_group_repeater = array(
		'key'          => 'tm_group_newsletter',
		'label'        => __( 'Newsletter Group', 'tm-user-area' ),
		'name'         => 'group_newsletter',
		'type'         => 'repeater',
		'layout'       => 'block',
		'sub_fields'   => array(
			$newsletter_title,
			$newsletter_description,
			$newsletter_image,
			$newsletter_options,
		),
		'button_label' => __( 'Add Group', 'tm-user-area' ),
	);

	$subscription_list_guid = array(
		'key'     => 'tm_subscription_list_guid',
		'label'   => __( 'GUID', 'tm-user-area' ),
		'name'    => 'subscription_list_guid',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '50',
		),
	);

	$subscription_list_id = array(
		'key'     => 'tm_subscription_list_id',
		'label'   => __( 'ID', 'tm-user-area' ),
		'name'    => 'subscription_list_id',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '50',
		),
	);

	$subscription_list_name = array(
		'key'     => 'tm_subscription_list_name',
		'label'   => __( 'Name', 'tm-user-area' ),
		'name'    => 'subscription_list_name',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '50',
		),
	);

	$subscription_list_slug = array(
		'key'     => 'tm_subscription_list_slug',
		'label'   => __( 'Slug', 'tm-user-area' ),
		'name'    => 'subscription_list_slug',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '50',
		),
	);

	$subscription_list = array(
		'key'          => 'tm_subscription_list',
		'label'        => __( 'Subscription List', 'tm-user-area' ),
		'name'         => 'subscription_list',
		'instructions' => __( 'Add your subscription options here. Each option will have a name, slug, guid, and id.', 'tm-user-area' ),
		'type'         => 'repeater',
		'layout'       => 'block',
		'sub_fields'   => array(
			$subscription_list_guid,
			$subscription_list_id,
			$subscription_list_name,
			$subscription_list_slug,
		),
		'button_label' => __( 'Add Subscription', 'tm-user-area' ),
	);

	$my_subscription_upgrade = array(
		'key'     => 'tm_subscription_upgrade',
		'label'   => __( 'Message for upgrade plan.', 'tm-user-area' ),
		'name'    => 'subscription_upgrade',
		'type'    => 'wysiwyg',
		'wrapper' => array(
			'width' => '50',
		),
	);

	$no_subscription_message = array(
		'key'     => 'tm_no_subscription_message',
		'label'   => __( 'Message for users without a plan.', 'tm-user-area' ),
		'name'    => 'no_subscription_message',
		'type'    => 'wysiwyg',
		'wrapper' => array(
			'width' => '50',
		),
	);

	// Tabs.
	$tab_contact_information = array(
		'key'       => 'tm_tab_contact_information',
		'label'     => __( 'Support Centre', 'tm-user-area' ),
		'type'      => 'tab',
		'placement' => 'left',
	);

	$tab_dashboard = array(
		'key'       => 'tm_tab_dashboard',
		'label'     => __( 'Dashboard', 'tm-user-area' ),
		'type'      => 'tab',
		'placement' => 'left',
	);

	$tab_header_icons = array(
		'key'       => 'tm_tab_header_icons',
		'label'     => __( 'Header icons', 'tm-user-area' ),
		'type'      => 'tab',
		'placement' => 'left',
	);

	$tab_newsletter = array(
		'key'       => 'tm_tab_newsletter',
		'label'     => __( 'Newsletter', 'tm-user-area' ),
		'type'      => 'tab',
		'placement' => 'left',
	);

	$tab_subscription = array(
		'key'       => 'tm_tab_subscription',
		'label'     => __( 'Subscription', 'tm-user-area' ),
		'type'      => 'tab',
		'placement' => 'left',
	);

	// Register the ACF fields.
	$acf_fields = array(
		$tab_dashboard,
		$dashboard_client_repeater,
		$dashboard_subscriber_repeater,
		$tab_contact_information,
		$support_contact_information,
		$support_center_links_repeater,
		$tab_header_icons,
		$support_contact_icon_link,
		$favourites_number_of_items,
		$notifications_number_of_items,
		$tab_newsletter,
		$newsletter_group_repeater,
		$subscription_list,
		$tab_subscription,
		$my_subscription_upgrade,
		$no_subscription_message,
	);

	acf_add_local_field_group(
		array(
			'key'      => 'tm_group_user_area_options',
			'title'    => __( 'User Area Group', 'tm-user-area' ),
			'fields'   => $acf_fields,
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tm-user-area',
					),
				),
			),
			'style'    => 'default',
		)
	);
}


/**
 * Registers the User Area Tab for URL Settings.
 *
 * @return void
 */
function register_url_settings_tab_options(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$tab_url_settings = array(
		'key'       => 'tm_tab_url_userarea',
		'label'     => __( 'User area', 'tm-user-area' ),
		'type'      => 'tab',
		'placement' => 'left',
		'parent'    => 'group_tm_url_settings', // Asociar al grupo de campos base.
	);

	$message_field_url_userarea = array(
		'key'     => 'field_tamarind_message_url_userarea',
		'name'    => 'message_url_userarea',
		'type'    => 'message',
		'message' => __( '<h3>Config dynamic URL</h3>Define URLs for <b>User area pages</b>.', 'tm-user-area' ),
		'parent'  => 'group_tm_url_settings', // Asociar al grupo de campos base.
	);

	// Register the URL settings.
	$urls_settings = register_url_repeater_settings();

	// Register the User Area Tab fields for URL Settings.
	acf_add_local_field( $tab_url_settings );
	acf_add_local_field( $message_field_url_userarea );
	acf_add_local_field( $urls_settings );
}

/**
 * Populate the select field with the clients.
 *
 * @param array $field The field.
 *
 * @return array
 */
function populate_select_dashboard_option_field( array $field ): array {
	if ( get_field( 'url_settings', 'option' ) ) {
		$url_settings = get_field( 'url_settings', 'option' );
		if ( $url_settings ) {
			$choices = array();
			foreach ( $url_settings as $url_setting ) {
				$choices[ $url_setting['url_key_slug'] ] = $url_setting['url_label'] . ' | ' . $url_setting['url_slug'];
			}
			$field['choices'] = $choices;
		}
	}

	return $field;
}

/**
 * Populate the select field with the subscription list.
 *
 * @param array $field The field.
 *
 * @return array
 */
function populate_select_subscription_list_field( array $field ): array {
	if ( get_field( 'subscription_list', 'option' ) ) {
		$subscription_list = get_field( 'subscription_list', 'option' );
		if ( $subscription_list ) {
			$choices = array(
				'' => '-----',
			);
			foreach ( $subscription_list as $list ) {
				$choices[ $list['subscription_list_id'] ] = $list['subscription_list_name'];
			}
			$field['choices'] = $choices;
		}
	}

	return $field;
}
