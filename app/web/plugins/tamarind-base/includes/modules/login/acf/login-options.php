<?php

namespace tamarind_base\login;

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', __NAMESPACE__ . '\register_login_fields' );
function register_login_fields(): void {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_options_page( array(
        'page_title'  => __( 'General', TM_LANGUAGE_DOMAIN ),
        'menu_slug'   => TM_LOGIN_OPTIONS_PAGE_SLUG,
        'parent_slug' => 'tamarind-base',
        'menu_title'  => esc_html__( 'Login', TM_LANGUAGE_DOMAIN ),
        'position'    => 100,
        'redirect'    => false,
    ) );

    require_once TM_LOGIN_MODULE_PATH . '/acf/tabs/page.php';
    require_once TM_LOGIN_MODULE_PATH . '/acf/tabs/background.php';
    require_once TM_LOGIN_MODULE_PATH . '/acf/tabs/container.php';
    require_once TM_LOGIN_MODULE_PATH . '/acf/tabs/form.php';
    require_once TM_LOGIN_MODULE_PATH . '/acf/tabs/logo.php';
    require_once TM_LOGIN_MODULE_PATH . '/acf/tabs/messages.php';
    require_once TM_LOGIN_MODULE_PATH . '/acf/tabs/labels.php';
    require_once TM_LOGIN_MODULE_PATH . '/acf/tabs/security.php';

    acf_add_local_field_group(
        array(
            'key'                   => 'group_login_tm_login_options',
            'title'                 => __( 'Login options', TM_LANGUAGE_DOMAIN ),
            'fields'                => array(
                $field_login_page_styles_tab,
                $field_customize_login_page,
                $field_login_layout,
                $field_background_color,
                $field_text_color,
                $field_link_color,
                $field_login_page_background_tab,
                $field_login_background_image,
                $field_login_background_position,
                $field_login_background_repeat,
                $field_login_background_size,
                $field_login_container_styles_tab,
                $field_login_container_width,
                $field_login_container_width_unit,
                $field_container_background_color,
                $field_login_form_styles_tab,
                $field_login_form_width,
                $field_login_form_width_unit,
                $field_form_background_color,
                $field_login_border_width,
                $field_login_border_width_unit,
                $field_form_border_color,
                $field_login_form_border_radius,
                $field_login_form_border_radius_unit,
                $field_login_form_padding,
                $field_login_form_padding_unit,
                $field_login_form_field_border_width,
                $field_login_form_field_border_unit,
                $field_form_field_border_color,
                $field_login_form_field_border_radius,
                $field_login_form_field_border_radius_unit,
                $field_login_submit_button_bg_color,
                $field_submit_button_text_color,
                $field_default_button_background_color,
                $field_default_button_text_color,
                $field_login_logo_tab,
                $field_login_use_custom_logo,
                $field_login_custom_logo,
                $field_login_logo_width,
                $field_login_logo_width_unit,
                $field_login_logo_height,
                $field_login_logo_height_unit,
                $field_login_messages_colors_tab,
                $field_login_success_color,
                $field_login_error_color,
                $field_login_warning_color,
                $field_login_info_color,
                $field_login_labels_tab,
                $field_login_customize_labels,
                $field_login_username_label,
                $field_login_username_show_label,
                $field_login_username_placeholder,
                $field_login_password_label,
                $field_login_password_show_label,
                $field_login_password_placeholder,
                $field_login_lost_password_label,
                $field_login_lost_password_position,
                $field_login_submit_label,
                $field_login_registration_text,
                $field_login_security_tab,
                $field_login_authenticated_users_only,
            ),
            'location'              => array(
        array(
            array(
                'param'    => 'options_page',
                'operator' => '==',
                'value'    => TM_LOGIN_OPTIONS_PAGE_SLUG,
            ),
        ),
    ),
            'menu_order'            => 0,
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

