<?php
defined('ABSPATH') || exit;

$field_login_labels_tab = array(
    'key' => 'field_login_labels_tab',
    'label' => __('Labels', TM_LANGUAGE_DOMAIN),
    'name' => 'tm_login_',
    'type' => 'tab',
    'placement' => 'left',
);

$field_login_customize_labels = array(
    'key' => 'field_login_customize_labels',
    'label' => esc_html__('Customize labels', TM_LANGUAGE_DOMAIN),
    'name' => 'tm_login_customize_labels',
    'type' => 'true_false',
    'default_value' => 0,
    'ui' => 1,
);

$field_login_username_label = array(
    'key' => 'field_login_username_label',
    'label' => esc_html__('Username or Email label', TM_LANGUAGE_DOMAIN),
    'name' => 'tm_login_label_username',
    'type' => 'text',
    'wrapper' => array(
        'width' => '50',
    ),
    'default_value' => __('Username', TM_LANGUAGE_DOMAIN),
    'placeholder' => __('Username', TM_LANGUAGE_DOMAIN),
);

$field_login_username_show_label = array(
    'key' => 'field_login_username_show_label',
    'label' => esc_html__('Show username label', TM_LANGUAGE_DOMAIN),
    'name' => 'tm_login_username_show_label',
    'type' => 'true_false',
    'wrapper' => array(
        'width' => '25',
    ),
    'default_value' => 1,
    'ui' => 1,
    'choices' => array(
        'inline-block' => __('Yes', TM_LANGUAGE_DOMAIN),
        'none' => __('No', TM_LANGUAGE_DOMAIN),
    ),
);

$field_login_username_placeholder = array(
    'key' => 'field_login_username_placeholder',
    'label' => esc_html__('Username or Email placeholder', TM_LANGUAGE_DOMAIN),
    'name' => 'tm_login_placeholder_username',
    'type' => 'text',
    'wrapper' => array(
        'width' => '25',
    ),
    'default_value' => __('Username or Email', TM_LANGUAGE_DOMAIN),
    'placeholder' => __('Username or Email', TM_LANGUAGE_DOMAIN),
);

$field_login_password_label = array(
    'key'           => 'field_login_password_label',
    'label'         => esc_html__( 'Password label', TM_LANGUAGE_DOMAIN ),
    'name'          => 'tm_login_label_password',
    'type'          => 'text',
    'default_value' => __( 'Password', TM_LANGUAGE_DOMAIN ),
    'wrapper'       => array(
        'width' => '50',
    ),
);

$field_login_password_show_label = array(
    'key' => 'field_login_password_show_label',
    'label' => esc_html__('Show password label', TM_LANGUAGE_DOMAIN),
    'name' => 'tm_login_password_show_label',
    'type' => 'true_false',
    'wrapper' => array(
        'width' => '25',
    ),
    'default_value' => 1,
    'ui' => 1,
);

$field_login_password_placeholder = array(
    'key' => 'field_login_password_placeholder',
    'label' => esc_html__('Password placeholder', TM_LANGUAGE_DOMAIN),
    'name' => 'tm_login_placeholder_password',
    'type' => 'text',
    'wrapper' => array(
        'width' => '25',
    ),
    'default_value' => __('Password', TM_LANGUAGE_DOMAIN),
    'placeholder' => __('Password', TM_LANGUAGE_DOMAIN),
);

$field_login_lost_password_label = array(
    'key'           => 'field_login_lost_password_label',
    'label'         => esc_html__( 'Lost your password? label', TM_LANGUAGE_DOMAIN ),
    'name'          => 'tm_login_label_lost_password',
    'type'          => 'text',
    'default_value' => __( 'Lost your password?', TM_LANGUAGE_DOMAIN ),
    'wrapper'       => array(
        'width' => '50',
    ),
);

$field_login_lost_password_position = array(
    'key' => 'field_login_lost_password_position',
    'label' => esc_html__('Lost your password? position', TM_LANGUAGE_DOMAIN),
    'name' => 'tm_login_lost_password_position',
    'type' => 'select',
    'choices' => array(
        'before_submit' => __('Before submit button', TM_LANGUAGE_DOMAIN),
        'after_submit' => __('After submit button', TM_LANGUAGE_DOMAIN),
    ),
    'default_value' => 'after_submit',
    'wrapper' => array(
        'width' => '50',
    ),
);

$field_login_submit_label = array(
    'key'           => 'field_login_submit_label',
    'label'         => esc_html__( 'Submit button label', TM_LANGUAGE_DOMAIN ),
    'name'          => 'tm_login_label_submit',
    'type'          => 'text',
    'default_value' => __( 'Log In', TM_LANGUAGE_DOMAIN ),
);

$field_login_reset_password_label = array(
    'key'           => 'field_login_reset_password_label',
    'label'         => esc_html__( 'Reset Password button label', TM_LANGUAGE_DOMAIN ),
    'name'          => 'tm_login_label_reset_password',
    'type'          => 'text',
    'default_value' => __( 'Get New Password', TM_LANGUAGE_DOMAIN ),
);

$field_login_registration_text = array(
    'key'           => 'field_login_registration_text',
    'label'         => esc_html__( 'Registration Text', TM_LANGUAGE_DOMAIN ),
    'name'          => 'tm_login_registration_text',
    'type'          => 'wysiwyg',
    'toolbar'       => 'basic',
    'media_upload'  => 0,
    'default_value' => '',
    'tabs'          => 'visual',
    'wrapper'       => array(
        'width' => '100',
    ),
);