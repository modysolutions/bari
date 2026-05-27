<?php defined( 'ABSPATH' ) || exit;

$field_login_page_background_tab = array(
    'key'       => 'field_login_page_background_tab',
    'label'     => __( 'Page background image', TM_LANGUAGE_DOMAIN ),
    'name'      => 'login_page_background_tab',
    'type'      => 'tab',
    'placement' => 'left',
);

$field_login_background_image = array(
    'key'               => 'field_login_background_image',
    'label'             => esc_html__( 'Body Background image', TM_LANGUAGE_DOMAIN ),
    'name'              => 'tm_login_body_background_image',
    'type'              => 'image',
    'return_format'     => 'url',
    'library'           => 'all',
    'preview_size'      => 'medium_large',
    'wrapper'           => array(
        'width' => '50',
        'class' => '',
        'id'    => '',
    ),
);

$field_login_background_position = array(
    'key' => 'field_login_background_position',
    'label' => esc_html__( 'Background position', TM_LANGUAGE_DOMAIN ),
    'name' => 'tm_login_background_position',
    'type' => 'select',
    'choices' => array(
        'left top'      => __('Left top', TM_LANGUAGE_DOMAIN),
        'left center'   => __('Left center', TM_LANGUAGE_DOMAIN),
        'left bottom'   => __('Left bottom', TM_LANGUAGE_DOMAIN),
        'center top'    => __('Center top', TM_LANGUAGE_DOMAIN),
        'center center' => __('Center center', TM_LANGUAGE_DOMAIN),
        'center bottom' => __('Center bottom', TM_LANGUAGE_DOMAIN),
        'right top'     => __('Right top', TM_LANGUAGE_DOMAIN),
        'right center'  => __('Right center', TM_LANGUAGE_DOMAIN),
        'right bottom'  => __('Right bottom', TM_LANGUAGE_DOMAIN),
    ),
    'default_value' => 'center center',
    'wrapper' => array(
        'width' => '50',
        'class' => '',
        'id'    => '',
    ),
);

$field_login_background_repeat = array(
    'key' => 'field_login_background_repeat',
    'label' => esc_html__( 'Background repeat', TM_LANGUAGE_DOMAIN ),
    'name' => 'tm_login_background_repeat',
    'type' => 'select',
    'choices' => array(
        'no-repeat' => __('No repeat', TM_LANGUAGE_DOMAIN),
        'repeat'    => __('Repeat', TM_LANGUAGE_DOMAIN),
        'repeat-x'  => __('Repeat horizontally', TM_LANGUAGE_DOMAIN),
        'repeat-y'  => __('Repeat vertically', TM_LANGUAGE_DOMAIN),
    ),
    'default_value' => 'no-repeat',
    'wrapper' => array(
        'width' => '50',
        'class' => '',
        'id'    => '',
    ),
);

$field_login_background_size = array(
    'key' => 'field_login_background_size',
    'label' => esc_html__( 'Background size', TM_LANGUAGE_DOMAIN ),
    'name' => 'tm_login_background_size',
    'type' => 'select',
    'choices' => array(
        'auto'    => __('Auto', TM_LANGUAGE_DOMAIN),
        'cover'   => __('Cover', TM_LANGUAGE_DOMAIN),
        'contain' => __('Contain', TM_LANGUAGE_DOMAIN),
    ),
    'default_value' => 'cover',
    'wrapper' => array(
        'width' => '50',
        'class' => '',
        'id'    => '',
    ),
);