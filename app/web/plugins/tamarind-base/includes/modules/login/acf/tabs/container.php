<?php defined( 'ABSPATH' ) || exit;

$field_login_container_styles_tab = array(
    'key'       => 'field_login_container_styles_tab',
    'label'     => __( 'Container styles', TM_LANGUAGE_DOMAIN ),
    'name'      => 'login_container_styles_tab',
    'type'      => 'tab',
    'placement' => 'left',
);

$field_login_container_width = array(
    'key'               => 'field_login_container_width',
    'label'             => esc_html__( 'Container width', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_container_width',
    'aria-label'        => '',
    'type'              => 'number',
    'instructions'      => '',
    'required'          => 0,
    'conditional_logic' => 0,
    'wrapper'           => array(
        'width' => '33',
        'class' => '',
        'id'    => '',
    ),
    'default_value'     => 320,
    'min'               => '',
    'max'               => '',
    'placeholder'       => '',
    'step'              => '',
    'prepend'           => '',
    'append'            => '',
);

$field_login_container_width_unit = array(
    'key'               => 'field_login_container_width_unit',
    'label'             => esc_html__( 'Unit', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_container_width_unit',
    'aria-label'        => '',
    'type'              => 'select',
    'instructions'      => '',
    'required'          => 0,
    'conditional_logic' => 0,
    'wrapper'           => array(
        'width' => '33',
        'class' => '',
        'id'    => '',
    ),
    'choices'           => array(
        '%'   => '%',
        'px'  => 'px',
        'em'  => 'em',
        'rem' => 'rem',
        'vw'  => 'vw',
    ),
    'default_value'     => 'px',
    'return_format'     => 'value',
    'multiple'          => 0,
    'allow_null'        => 0,
    'ui'                => 0,
    'ajax'              => 0,
    'placeholder'       => '',
);

$field_container_background_color = array(
    'key'               => 'field_login_container_background_color',
    'label'             => esc_html__( 'Container background Color', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_container_background_color',
    'aria-label'        => '',
    'type'              => 'color_picker',
    'instructions'      => '',
    'required'          => 0,
    'conditional_logic' => 0,
    'wrapper'           => array(
        'width' => '33',
        'class' => '',
        'id'    => '',
    ),
    'default_value'     => '#FFFFFF',
    'enable_opacity'    => 0,
    'return_format'     => 'string',
);
