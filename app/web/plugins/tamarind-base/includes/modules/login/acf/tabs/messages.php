<?php defined('ABSPATH') || exit;

$field_login_messages_colors_tab = array(
    'key'       => 'field_login_messages_colors_tab',
    'label'     => __( 'Messages', TM_LANGUAGE_DOMAIN ),
    'name'      => '',
    'type'      => 'tab',
    'placement' => 'left',
);

$field_login_success_color = array(
    'key'               => 'field_login_login_success_color',
    'label'             => esc_html__( 'Success', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_notice_success',
    'aria-label'        => '',
    'type'              => 'color_picker',
    'instructions'      => '',
    'required'          => 0,
    'conditional_logic' => 0,
    'wrapper'           => array(
        'width' => '50',
        'class' => '',
        'id'    => '',
    ),
    'default_value'     => '#00a32a',
    'enable_opacity'    => 0,
    'return_format'     => 'string',
);

$field_login_error_color = array(
    'key'               => 'field_login_login_error_color',
    'label'             => esc_html__( 'Error', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_notice_error',
    'aria-label'        => '',
    'type'              => 'color_picker',
    'instructions'      => '',
    'required'          => 0,
    'conditional_logic' => 0,
    'wrapper'           => array(
        'width' => '50',
        'class' => '',
        'id'    => '',
    ),
    'default_value'     => '#d63638',
    'enable_opacity'    => 0,
    'return_format'     => 'string',
);

$field_login_warning_color = array(
    'key'               => 'field_login_login_warning_color',
    'label'             => esc_html__( 'Warning', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_notice_warning',
    'aria-label'        => '',
    'type'              => 'color_picker',
    'instructions'      => '',
    'required'          => 0,
    'conditional_logic' => 0,
    'wrapper'           => array(
        'width' => '50',
        'class' => '',
        'id'    => '',
    ),
    'default_value'     => '#dba617',
    'enable_opacity'    => 0,
    'return_format'     => 'string',
);

$field_login_info_color = array(
    'key'               => 'field_login_login_info_color',
    'label'             => esc_html__( 'Info', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_notice_info',
    'aria-label'        => '',
    'type'              => 'color_picker',
    'instructions'      => '',
    'required'          => 0,
    'conditional_logic' => 0,
    'wrapper'           => array(
        'width' => '50',
        'class' => '',
        'id'    => '',
    ),
    'default_value'     => '#72aee6',
    'enable_opacity'    => 0,
    'return_format'     => 'string',
);