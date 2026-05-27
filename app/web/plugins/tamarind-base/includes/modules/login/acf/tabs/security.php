<?php defined( 'ABSPATH' ) || exit;

$field_login_security_tab = array(
    'key'       => 'field_login_security_tab',
    'label'     => __( 'Security', TM_LANGUAGE_DOMAIN ),
    'name'      => '',
    'type'      => 'tab',
    'placement' => 'left',
);

$field_login_authenticated_users_only = array(
    'key'               => 'field_login_authenticated_users_only',
    'label'             => esc_html__( 'Authenticated users only', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_authenticated_users_only',
    'aria-label'        => '',
    'type'              => 'true_false',
    'instructions'      => '',
    'required'          => 0,
    'conditional_logic' => 0,
    'wrapper'           => array(
        'width' => '',
        'class' => '',
        'id'    => '',
    ),
    'message'           => esc_html__( 'Only allow authenticated users to access the login page. If enabled, non-authenticated users will be redirected to the homepage.', TM_LANGUAGE_DOMAIN ),
    'default_value'     => 0,
    'ui_on_text'        => '',
    'ui_off_text'       => '',
    'ui'                => 1,
);