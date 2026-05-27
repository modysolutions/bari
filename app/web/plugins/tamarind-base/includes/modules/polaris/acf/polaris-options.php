<?php
/**
 * ACF Options for Polaris Widget.
 *
 * Registers a field group on the shared "Widgets" options page (tm-widgets).
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\polaris;

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', __NAMESPACE__ . '\register_polaris_acf_fields' );
add_filter( 'acf/validate_value/key=field_plr_target', __NAMESPACE__ . '\validate_polaris_landing_target', 10, 4 );

/**
 * Register ACF fields for the Polaris navigation widget.
 */
function register_polaris_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$general_tab = array(
		'key'       => 'field_plr_tab_01',
		'label'     => __( 'General', TM_LANGUAGE_DOMAIN ),
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$appearance_tab = array(
		'key'       => 'field_plr_tab_03',
		'label'     => __( 'Appearance', TM_LANGUAGE_DOMAIN ),
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$labels_tab = array(
		'key'       => 'field_plr_tab_02',
		'label'     => __( 'Labels', TM_LANGUAGE_DOMAIN ),
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'left',
	);

	// -------------------------------------------------------------------------
	// General tab fields
	// -------------------------------------------------------------------------

	$polaris_enabled = array(
		'key'           => 'field_plr_enabled',
		'label'         => __( 'Enable Polaris Widget', TM_LANGUAGE_DOMAIN ),
		'name'          => 'polaris_enabled',
		'type'          => 'true_false',
		'instructions'  => __( 'Activate the floating navigation widget on this site.', TM_LANGUAGE_DOMAIN ),
		'wrapper'       => array( 'width' => '20' ),
		'default_value' => 0,
		'ui'            => 1,
	);

	$polaris_landing_target = array(
		'key'          => 'field_plr_target',
		'label'        => __( 'Landing Content', 'tamarind-base' ),
		'name'         => 'polaris_landing_target',
		'type'         => 'post_object',
		'instructions' => __( 'Select the internal page or content the widget should link to. The widget resolves the correct URL automatically for local, stage and production environments.', 'tamarind-base' ),
		'wrapper'      => array( 'width' => '50' ),
		'post_type'    => array( 'post', 'page' ),
		'return_format' => 'id',
		'ui'            => 1,
	);

	$polaris_position = array(
		'key'     => 'field_plr_position',
		'label'   => __( 'Widget Position', 'tamarind-base' ),
		'name'    => 'polaris_position',
		'type'    => 'select',
		'wrapper' => array( 'width' => '15' ),
		'choices' => array(
			'right' => __( 'Right', 'tamarind-base' ),
			'left'  => __( 'Left', 'tamarind-base' ),
		),
		'default_value' => 'right',
		'return_format' => 'value',
	);

	$polaris_device = array(
		'key'     => 'field_plr_device',
		'label'   => __( 'Device Visibility', 'tamarind-base' ),
		'name'    => 'polaris_device_visibility',
		'type'    => 'select',
		'wrapper' => array( 'width' => '15' ),
		'choices' => array(
			'all'     => __( 'All devices', 'tamarind-base' ),
			'mobile'  => __( 'Mobile only', 'tamarind-base' ),
			'desktop' => __( 'Desktop only', 'tamarind-base' ),
		),
		'default_value' => 'all',
		'return_format' => 'value',
	);

	$polaris_bottom_offset = array(
		'key'          => 'field_plr_bottom',
		'label'        => __( 'Bottom Offset (px)', 'tamarind-base' ),
		'name'         => 'polaris_bottom_offset',
		'type'         => 'number',
		'instructions' => __( 'Distance from the bottom of the screen in pixels. Increase to raise the widget above other fixed elements.', 'tamarind-base' ),
		'wrapper'      => array( 'width' => '15' ),
		'default_value' => 32,
		'min'           => 0,
		'step'          => 1,
		'append'        => 'px',
	);

	$polaris_user_visibility = array(
		'key'          => 'field_plr_user_vis',
		'label'        => __( 'User Visibility', 'tamarind-base' ),
		'name'         => 'polaris_user_visibility',
		'type'         => 'select',
		'instructions' => __( 'Choose which users can see the widget based on their login status.', 'tamarind-base' ),
		'wrapper'      => array( 'width' => '20' ),
		'choices'      => array(
			'all'        => __( 'All users', 'tamarind-base' ),
			'logged_in'  => __( 'Logged-in only', 'tamarind-base' ),
			'logged_out' => __( 'Logged-out only', 'tamarind-base' ),
		),
		'default_value' => 'all',
		'return_format' => 'value',
	);

	// -------------------------------------------------------------------------
	// Labels tab fields
	// -------------------------------------------------------------------------

	$polaris_label_to_landing = array(
		'key'          => 'field_plr_lbl_go',
		'label'        => __( 'Button Label — Go to Landing', 'tamarind-base' ),
		'name'         => 'polaris_label_to_landing',
		'type'         => 'text',
		'instructions' => __( 'Visible button label when the visitor is on the homepage and the widget navigates to the landing page.', 'tamarind-base' ),
		'wrapper'      => array( 'width' => '50' ),
		'placeholder'  => __( 'Learn more', 'tamarind-base' ),
	);

	$polaris_label_to_home = array(
		'key'          => 'field_plr_lbl_bk',
		'label'        => __( 'Button Label — Back to Home', 'tamarind-base' ),
		'name'         => 'polaris_label_to_home',
		'type'         => 'text',
		'instructions' => __( 'Visible button label when the visitor is on the landing page and the widget navigates back to the homepage.', 'tamarind-base' ),
		'wrapper'      => array( 'width' => '50' ),
		'placeholder'  => __( 'Back to home', 'tamarind-base' ),
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_polaris00000001',
			'title'                 => __( 'Polaris Widget', 'tamarind-base' ),
			'fields'                => array(
				$general_tab,
				$polaris_enabled,
				$polaris_landing_target,
				$polaris_user_visibility,
				$polaris_device,
				$appearance_tab,
				$polaris_position,
				$polaris_bottom_offset,
				$labels_tab,
				$polaris_label_to_landing,
				$polaris_label_to_home,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tm-widgets',
					),
				),
			),
			'menu_order'            => 1,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'field',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
		)
		);
}

/**
 * Prevent selecting the static homepage as the Polaris landing target.
 *
 * @param bool|string $valid Whether the value is valid.
 * @param mixed       $value Submitted field value.
 * @param array       $field Field settings.
 * @param string      $input Input name attribute.
 * @return bool|string
 */
function validate_polaris_landing_target( $valid, $value, $field, $input ) {
	unset( $field, $input );

	if ( true !== $valid ) {
		return $valid;
	}

	$target_id = absint( $value );
	if ( ! $target_id ) {
		return $valid;
	}

	if ( ! is_static_homepage_target( $target_id ) ) {
		return $valid;
	}

	return __( 'Landing Content cannot be the homepage.', 'tamarind-base' );
}

/**
 * Check whether a post ID matches the static homepage setting.
 *
 * @param int $target_id Selected landing target ID.
 * @return bool
 */
function is_static_homepage_target( $target_id ) {
	$front_page_id = (int) get_option( 'page_on_front' );

	if ( ! $front_page_id || 'page' !== get_option( 'show_on_front' ) ) {
		return false;
	}

	return $front_page_id === (int) $target_id;
}
