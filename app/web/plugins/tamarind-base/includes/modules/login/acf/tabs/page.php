<?php defined( 'ABSPATH' ) || exit;

$field_login_page_styles_tab = array(
    'key'       => 'field_login_page_styles_tab',
    'label'     => __( 'Page styles', TM_LANGUAGE_DOMAIN ),
    'name'      => 'login_page_styles_tab',
    'type'      => 'tab',
    'placement' => 'left',
);

$field_customize_login_page = array(
    'key'               => 'field_login_customize_login_page',
    'label'             => esc_html__( 'Customize login page', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_customize_login_page',
    'aria-label'        => '',
    'type'              => 'true_false',
    'instructions'      => '',
    'required'          => 0,
    'conditional_logic' => 0,
    'wrapper'           => array(
        'width' => '50',
        'class' => '',
        'id'    => '',
    ),
    'message'           => '',
    'default_value'     => 0,
    'ui_on_text'        => '',
    'ui_off_text'       => '',
    'ui'                => 1,
);

$field_login_layout = array(
    'key'               => 'field_login_layout',
    'label'             => esc_html__( 'Layout', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_layout',
    'aria-label'        => '',
    'type'              => 'select',
    'instructions'      => __('Sets the position of the login/register/forgot password box', TM_LANGUAGE_DOMAIN),
    'required'          => 0,
    'conditional_logic' => 0,
    'wrapper'           => array(
        'width' => '50',
        'class' => '',
        'id'    => '',
    ),
    'choices'           => array(
        'center' => __('Center', TM_LANGUAGE_DOMAIN),
        'left'   => __('Left', TM_LANGUAGE_DOMAIN),
        'right'  => __('Right', TM_LANGUAGE_DOMAIN),
    ),
    'default_value'     => 'center',
    'return_format'     => 'value',
    'multiple'          => 0,
    'allow_null'        => 0,
    'ui'                => 1,
    'ajax'              => 0,
    'placeholder'       => '',
);

$field_background_color = array(
    'key'               => 'field_login_background_color',
    'label'             => esc_html__( 'Barckground color', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_background_color',
    'aria-label'        => '',
    'type'              => 'color_picker',
    'instructions'      => '',
    'required'          => 0,
    'conditional_logic' => 0,
    'wrapper'           => array(
        'width' => '33.33',
        'class' => '',
        'id'    => '',
    ),
    'default_value'     => '#FFFFFF',
    'enable_opacity'    => 1,
    'return_format'     => 'string',
);

$field_text_color = array(
    'key'               => 'field_login_text_color',
    'label'             => esc_html__( 'Text Color', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_text_color',
    'aria-label'        => '',
    'type'              => 'color_picker',
    'instructions'      => '',
    'required'          => 0,
    'conditional_logic' => 0,
    'wrapper'           => array(
        'width' => '33.33',
        'class' => '',
        'id'    => '',
    ),
    'default_value'     => '#000000',
    'enable_opacity'    => 1,
    'return_format'     => 'string',
);

$field_link_color = array(
    'key'               => 'field_login_link_color',
    'label'             => esc_html__( 'Link Color', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_link_color',
    'aria-label'        => '',
    'type'              => 'color_picker',
    'instructions'      => '',
    'required'          => 0,
    'conditional_logic' => 0,
    'wrapper'           => array(
        'width' => '33.33',
        'class' => '',
        'id'    => '',
    ),
    'default_value'     => '#000000',
    'enable_opacity'    => 1,
    'return_format'     => 'string',
);