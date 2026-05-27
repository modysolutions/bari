<?php
/**
 * ACF Fields Options for Tamarind Forms.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\acf;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the ACF fields.
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	add_filter( 'acf/init', __NAMESPACE__ . '\register_acf_fields' );
}

/**
 * Registers the `technology` ACF fields.
 */
function register_acf_fields() {

	acf_add_options_page(
		array(
			'page_title'  => __('Form Settings', 'tamarind-forms'),
			'menu_title'  => __('Form Settings', 'tamarind-forms'),
			'menu_slug'   => 'tamarind-acf-forms-options',
			'capability'  => 'manage_options',
			'redirect'    => false,
			'parent_slug' => 'tamarind-base',
		)
	);

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// ACF for TYPES FORMS.
	$form_field_type_action_name = array(
		'key'             => 'field_tamarind_forms_type_action_name',
		'label'           => __( 'Name', 'tamarind-forms' ),
		'name'            => 'name',
		'type'            => 'text',
		'wrapper'         => array(
			'width' => '33',
		),
		'parent-repeater' => 'field_tamarind_forms_define_actions_forms',
	);

	$form_field_type_message = array(
		'key'             => 'field_tamarind_forms_type_message',
		'label'           => __( 'Note', 'tamarind-forms' ),
		'name'            => 'type_message',
		'type'            => 'message',
		'message'         => __( 'Define the forms actions. Title, description and Text Submit buttton for forms.', 'tamarind-forms' ),
		'wrapper'         => array(
			'width' => '33',
		),
		'parent-repeater' => 'field_tamarind_forms_define_actions_forms',
	);

	$form_field_bool_default = array(
		'key'             => 'field_tamarind_forms_bool_default',
		'label'           => __( 'Default', 'tamarind-forms' ),
		'name'            => 'default',
		'type'            => 'true_false',
		'wrapper'         => array(
			'width' => '34',
		),
		'style'           => 'default',
		'message'         => __( 'Set as default form.', 'tamarind-forms' ),
		'default_value'   => 0,
		'ui'              => 1,
		'ui_on_text'      => 'Yes',
		'ui_off_text'     => 'No',
		'parent-repeater' => 'field_tamarind_forms_define_actions_forms',
	);

	$form_field_type_title = array(
		'key'             => 'field_tamarind_forms_type_title',
		'label'           => __( 'Title', 'tamarind-forms' ),
		'name'            => 'type_title',
		'type'            => 'text',
		'wrapper'         => array(
			'width' => '33',
		),
		'parent-repeater' => 'field_tamarind_forms_define_actions_forms',
	);

	$form_field_type_description = array(
		'key'             => 'field_tamarind_forms_type_description',
		'label'           => __( 'Description', 'tamarind-forms' ),
		'name'            => 'type_description',
		'type'            => 'textarea',
		'wrapper'         => array(
			'width' => '34',
		),
		'parent-repeater' => 'field_tamarind_forms_define_actions_forms',
	);

	$form_field_type_submit_text = array(
		'key'             => 'field_tamarind_forms_type_submit_text',
		'label'           => __( 'Submit Text', 'tamarind-forms' ),
		'name'            => 'type_submit_text',
		'type'            => 'text',
		'wrapper'         => array(
			'width' => '33',
		),
		'parent-repeater' => 'field_tamarind_forms_define_actions_forms',
	);

	$form_field_define_actions_forms = array(
		'key'        => 'field_tamarind_forms_define_actions_forms',
		'label'      => __( 'Define Actions Forms', 'tamarind-forms' ),
		'name'       => 'define_actions_forms',
		'type'       => 'repeater',
		'layout'     => 'block',
		'sub_fields' => array(
			$form_field_type_action_name,
			$form_field_type_message,
			$form_field_bool_default,
			$form_field_type_submit_text,
			$form_field_type_title,
			$form_field_type_description,
		),
		'collapsed'  => 'field_tamarind_forms_type_action_name',
	);

	// ACF for DINAMIC FIELDS.
	$form_field_name = array(
		'key'             => 'field_tamarind_forms_field_name',
		'label'           => __( 'Name', 'tamarind-forms' ),
		'name'            => 'name',
		'type'            => 'text',
		'wrapper'         => array(
			'width' => '50',
		),
		'parent-repeater' => 'field_tamarind_forms_fields',
	);

	$form_field_gf_name = array(
		'key'             => 'field_tamarind_forms_field_gf_name',
		'label'           => __( 'GF Field Name', 'tamarind-forms' ),
		'name'            => 'gf_name',
		'type'            => 'text',
		'wrapper'         => array(
			'width' => '50',
		),
		'parent-repeater' => 'field_tamarind_forms_fields',
	);

	$form_field_option_name = array(
		'key'             => 'field_tamarind_forms_field_option_name',
		'label'           => __( 'Name', 'tamarind-forms' ),
		'name'            => 'option_name',
		'type'            => 'text',
		'wrapper'         => array(
			'width' => '50',
		),
		'parent-repeater' => 'field_tamarind_forms_field_options',
	);

	$form_field_option_value = array(
		'key'             => 'field_tamarind_forms_field_option_value',
		'label'           => __( 'Value', 'tamarind-forms' ),
		'name'            => 'option_value',
		'type'            => 'text',
		'wrapper'         => array(
			'width' => '50',
		),
		'parent-repeater' => 'field_tamarind_forms_field_options',
	);

	$form_field_options = array(
		'key'             => 'field_tamarind_forms_field_options',
		'label'           => __( 'Options', 'tamarind-forms' ),
		'name'            => 'options',
		'type'            => 'repeater',
		'layout'          => 'block',
		'parent-repeater' => 'field_tamarind_forms_fields',
		'sub_fields'      => array(
			$form_field_option_name,
			$form_field_option_value,
		),
		'button_label'    => 'Add Option',
	);

	$forms_dinamic_fields = array(
		'key'          => 'field_tamarind_forms_fields',
		'label'        => __( 'Fields', 'tamarind-forms' ),
		'name'         => 'forms_fields',
		'type'         => 'repeater',
		'layout'       => 'block',
		'sub_fields'   => array(
			$form_field_name,
			$form_field_gf_name,
			$form_field_options,
		),
		'collapsed'    => 'field_tamarind_forms_field_name',
		'button_label' => 'Add Field',
	);

	// ACF for ZOHO FIELDS.
	$form_zoho_action_field_name = array(
		'key'             => 'field_tamarind_forms_field_zoho_action_name',
		'label'           => __( 'Name', 'tamarind-forms' ),
		'name'            => 'action_name',
		'type'            => 'text',
		'wrapper'         => array(
			'width' => '50',
		),
		'parent-repeater' => 'field_tamarind_forms_zoho_fields',
	);

	$form_zoho_action_field_value = array(
		'key'             => 'field_tamarind_forms_field_zoho_action_value',
		'label'           => __( 'Value', 'tamarind-forms' ),
		'name'            => 'action_value',
		'type'            => 'text',
		'wrapper'         => array(
			'width' => '50',
		),
		'parent-repeater' => 'field_tamarind_forms_zoho_fields',
	);

	$forms_zoho_action_fields = array(
		'key'          => 'field_tamarind_forms_zoho_fields',
		'label'        => __( 'Actions', 'tamarind-forms' ),
		'name'         => 'zoho_actions',
		'type'         => 'repeater',
		'layout'       => 'block',
		'sub_fields'   => array(
			$form_zoho_action_field_name,
			$form_zoho_action_field_value,
		),
		'button_label' => 'Add Field',
	);

	$form_zoho_platform_field = array(
		'key'     => 'field_tamarind_forms_field_zoho_platform_name',
		'label'   => __( 'Platform', 'tamarind-forms' ),
		'name'    => 'platform',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '50',
		),
	);

	$form_zoho_submissions_field = array(
		'key'               => 'field_tamarind_forms_field_zoho_send_submissions',
		'label'             => __( 'Send data to Zoho Submissions', 'tamarind-forms' ),
		'name'              => 'zoho_submissions',
		'type'              => 'true_false',
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '50',
			'class' => '',
			'id'    => '',
		),
		'message'           => '',
		'default_value'     => 0,
		'ui'                => 1,
		'ui_on_text'        => 'Enabled',
		'ui_off_text'       => 'Disabled',
	);

	$form_zoho_api_url_field = array(
		'key'               => 'field_tamarind_forms_field_zoho_api_url',
		'label'             => __( 'API URL', 'tamarind-forms' ),
		'name'              => 'zoho_api_url',
		'type'              => 'url',
		'wrapper'           => array(
			'width' => '50',
		),
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'field_tamarind_forms_field_zoho_send_submissions',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
	);

	$form_zoho_api_key_field = array(
		'key'               => 'field_tamarind_forms_field_zoho_api_key',
		'label'             => __( 'API Key', 'tamarind-forms' ),
		'name'              => 'zoho_api_key',
		'type'              => 'text',
		'wrapper'           => array(
			'width' => '50',
		),
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'field_tamarind_forms_field_zoho_send_submissions',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
	);

	// ACF for FIELDS VALIDATION.
	$form_validate_action_field_domains = array(
		'key'             => 'field_tamarind_forms_field_validate_action_domains',
		'label'           => __( 'Blocked domain', 'tamarind-forms' ),
		'name'            => 'blocked_domain',
		'type'            => 'text',
		'parent-repeater' => 'field_tamarind_forms_validate_fields',
	);

	$forms_validate_action_fields = array(
		'key'          => 'field_tamarind_forms_validate_fields',
		'label'        => __( 'Blocked domains for email fields', 'tamarind-forms' ),
		'name'         => 'blocked_domains',
		'type'         => 'repeater',
		'layout'       => 'block',
		'sub_fields'   => array(
			$form_validate_action_field_domains,
		),
		'button_label' => 'Add Domain',
		'wrapper'      => array(
			'width' => '80',
		),
	);

	$form_validate_platform_field = array(
		'key'     => 'field_tamarind_forms_field_validate_platform_name',
		'label'   => __( 'Maximum input size', 'tamarind-forms' ),
		'name'    => 'max-size',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '20',
		),
	);

	// ACF for CONSENT.
	$form_consent_default_field = array(
		'key'     => 'field_tamarind_forms_field_consent_default',
		'label'   => __( 'Default Consent', 'tamarind-forms' ),
		'name'    => 'default_consent',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '50',
		),
	);

	$form_consent_newsletter_field = array(
		'key'     => 'field_tamarind_forms_field_consent_newsletter',
		'label'   => __( 'Newsletter Consent', 'tamarind-forms' ),
		'name'    => 'newsletter_consent',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '50',
		),
	);

	// ACF for NEWSLETTERS.
	$form_newsletter_footer_field = array(
		'key'          => 'field_tamarind_forms_field_newsletter_footer',
		'label'        => __( 'Newsletter Footer', 'tamarind-forms' ),
		'name'         => 'newsletter_footer',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_newsletter_post_field = array(
		'key'          => 'field_tamarind_forms_field_newsletter_post',
		'label'        => __( 'Newsletter Post', 'tamarind-forms' ),
		'name'         => 'newsletter_post_form',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_newsletter_events_field = array(
		'key'          => 'field_tamarind_forms_field_newsletter_events',
		'label'        => __( 'Newsletter Events', 'tamarind-forms' ),
		'name'         => 'newsletter_events_form',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	// ACF to events.
	$form_events_field = array(
		'key'          => 'field_tamarind_forms_field_events',
		'label'        => __( 'Events', 'tamarind-forms' ),
		'name'         => 'events_form',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	// ACF for RATE.
	$form_rate_field = array(
		'key'          => 'field_tamarind_forms_field_rate_form',
		'label'        => __( 'Rate', 'tamarind-forms' ),
		'name'         => 'rate_form',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	// ACF for downloads.
	$form_download_pdf_field = array(
		'key'          => 'field_tamarind_forms_field_download_pdf',
		'label'        => __( 'Download PDF', 'tamarind-forms' ),
		'name'         => 'download_pdf',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_free_sample_field = array(
		'key'          => 'field_tamarind_forms_field_free_sample',
		'label'        => __( 'Free Sample', 'tamarind-forms' ),
		'name'         => 'free_sample',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_download_product_field = array(
		'key'          => 'field_tamarind_forms_field_download_product',
		'label'        => __( 'Download Product', 'tamarind-forms' ),
		'name'         => 'download_product',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_download_product_sidebar_field = array(
		'key'          => 'field_tamarind_forms_field_download_product_sidebar',
		'label'        => __( 'Download Product Sidebar', 'tamarind-forms' ),
		'name'         => 'download_product_sidebar',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_download_free_report_field = array(
		'key'          => 'field_tamarind_forms_field_download_free_report',
		'label'        => __( 'Download Free Report', 'tamarind-forms' ),
		'name'         => 'download_free_report',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_need_help_field = array(
		'key'          => 'field_tamarind_forms_field_need_help',
		'label'        => __( 'Need Help', 'tamarind-forms' ),
		'name'         => 'need_help',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	// ACF for Contact.
	$form_contact_field = array(
		'key'          => 'field_tamarind_forms_field_contact',
		'label'        => __( 'Contact', 'tamarind-forms' ),
		'name'         => 'contact',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_chat_field = array(
		'key'          => 'field_tamarind_forms_field_chat',
		'label'        => __( 'Chat', 'tamarind-forms' ),
		'name'         => 'chat',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	// ACF for Trainings.
	$form_trainings_field = array(
		'key'          => 'field_tamarind_forms_field_trainings',
		'label'        => __( 'Trainings', 'tamarind-forms' ),
		'name'         => 'trainings',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	// ACF for China.
	$form_china_field = array(
		'key'          => 'field_tamarind_forms_field_china',
		'label'        => __( 'China', 'tamarind-forms' ),
		'name'         => 'china_form',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_china_label_email_field = array(
		'key'     => 'field_tamarind_forms_field_china_label_email',
		'label'   => __( 'Label Email', 'tamarind-forms' ),
		'name'    => 'china_label_email',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25',
		),
	);

	$form_china_label_company_field = array(
		'key'     => 'field_tamarind_forms_field_china_label_company',
		'label'   => __( 'Label Company', 'tamarind-forms' ),
		'name'    => 'china_label_company',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25',
		),
	);

	$form_china_label_first_name_field = array(
		'key'     => 'field_tamarind_forms_field_china_label_first_name',
		'label'   => __( 'Label First Name', 'tamarind-forms' ),
		'name'    => 'china_label_first_name',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25',
		),
	);

	$form_china_label_last_name_field = array(
		'key'     => 'field_tamarind_forms_field_china_label_last_name',
		'label'   => __( 'Label Last Name', 'tamarind-forms' ),
		'name'    => 'china_label_last_name',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25',
		),
	);

	$form_china_label_department_field = array(
		'key'     => 'field_tamarind_forms_field_china_label_department',
		'label'   => __( 'Label Department', 'tamarind-forms' ),
		'name'    => 'china_label_department',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25',
		),
	);

	$form_china_label_industry_field = array(
		'key'     => 'field_tamarind_forms_field_china_label_industry',
		'label'   => __( 'Label Industry', 'tamarind-forms' ),
		'name'    => 'china_label_industry',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25',
		),
	);

	$form_china_label_telephone_field = array(
		'key'     => 'field_tamarind_forms_field_china_label_telephone',
		'label'   => __( 'Label Telephone', 'tamarind-forms' ),
		'name'    => 'china_label_telephone',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25',
		),
	);

	$form_china_label_content_field = array(
		'key'     => 'field_tamarind_forms_field_china_label_content',
		'label'   => __( 'Label Content', 'tamarind-forms' ),
		'name'    => 'china_label_content',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25',
		),
	);

	$form_china_label_download_button_field = array(
		'key'     => 'field_tamarind_forms_field_china_label_download_button',
		'label'   => __( 'Label Download Button', 'tamarind-forms' ),
		'name'    => 'china_label_download_button',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25',
		),
	);

	$form_china_label_acceptance_field = array(
		'key'     => 'field_tamarind_forms_field_china_label_acceptance',
		'label'   => __( 'Label Acceptance', 'tamarind-forms' ),
		'name'    => 'china_label_acceptance',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '75',
		),
	);

	// ACF for Users.
	$form_key_register_page = array(
		'key'   => 'field_tamarind_forms_register_key',
		'label' => 'Register form key',
		'name'  => 'register_key',
		'type'  => 'text',
	);

	$form_user_label_register_page = array(
		'key'        => 'field_tamarind_forms_user_label_register_page',
		'label'      => 'Default Register form page',
		'name'       => 'user_label_register_page',
		'type'       => 'page_link',
		'post_type'  => array( 'page' ),
		'allow_null' => 0,
		'multiple'   => 0,
		'wrapper'    => array(
			'width' => '50',
		),
	);

	$form_user_label_login_page = array(
		'key'        => 'field_tamarind_forms_user_label_login_page',
		'label'      => 'Login form page',
		'name'       => 'user_label_login_page',
		'type'       => 'page_link',
		'post_type'  => array( 'page' ),
		'allow_null' => 0,
		'multiple'   => 0,
		'wrapper'    => array(
			'width' => '50',
		),
	);

	$form_user_label_forgot_password_page = array(
		'key'        => 'field_tamarind_forms_user_label_forgot_page',
		'label'      => 'Forgot password form page',
		'name'       => 'user_label_forgot_page',
		'type'       => 'page_link',
		'post_type'  => array( 'page' ),
		'allow_null' => 0,
		'multiple'   => 0,
		'wrapper'    => array(
			'width' => '50',
		),
	);

	$form_user_label_reset_password_page = array(
		'key'        => 'field_tamarind_forms_user_label_reset_page',
		'label'      => 'Reset password form page',
		'name'       => 'user_label_reset_page',
		'type'       => 'page_link',
		'post_type'  => array( 'page' ),
		'allow_null' => 0,
		'multiple'   => 0,
		'wrapper'    => array(
			'width' => '50',
		),
	);

	$form_user_label_enable_pre_checkout = array(
		'key'               => 'field_608ac0622250e',
		'label'             => 'Enable pre-checkout',
		'name'              => 'enable_pre-checkout',
		'type'              => 'true_false',
		'instructions'      => 'Enable page for login/registration before Checkout',
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '50',
			'class' => '',
			'id'    => '',
		),
		'message'           => '',
		'default_value'     => 0,
		'ui'                => 1,
		'ui_on_text'        => 'Enabled',
		'ui_off_text'       => 'Disabled',
	);

	$form_user_label_pre_checkout_page = array(
		'key'               => 'field_608ac07a2250f',
		'label'             => 'Pre-Checkout page',
		'name'              => 'pre-checkout_page',
		'type'              => 'post_object',
		'instructions'      => 'Select (Create before) page for login/registration before checkout. Ensure this page is noindex/nofollow. This page must contain the shortcode: [wc_login_register].',
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '50',
			'class' => '',
			'id'    => '',
		),
		'post_type'         => array( 'page' ), // Solo mostrará páginas.
		'taxonomy'          => '',
		'allow_null'        => 0,
		'multiple'          => 0,
		'return_format'     => 'id',
	);

	$form_register_onexit = array(
		'key'          => 'field_tamarind_forms_field_register_onexit',
		'label'        => __( 'Register on exit in Modal', 'tamarind-forms' ),
		'name'         => 'register_onexit',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$activate_register_onexit = array(
		'key'           => 'field_tamarind_forms_activate_register_onexit',
		'label'         => 'Enable this feature (if disabled, add ?testab to url to test)',
		'name'          => 'activate_register_onexit',
		'type'          => 'true_false',
		'instructions'  => 'Activate the register on exit in modal',
		'style'         => 'default',
		'default_value' => 1,
		'ui'            => 1,
		'ui_on_text'    => 'Yes',
		'ui_off_text'   => 'No',
		'wrapper'       => array(
			'width' => '50',
		),
	);

	$cookie_register_onexit = array(
		'key'           => 'field_tamarind_forms_cookie_register_onexit',
		'label'         => 'COOKIE expiration',
		'name'          => 'cookie_register_onexit',
		'type'          => 'number',
		'instructions'  => 'Set cookie expiration days to control how many times must be shown.\r\nLeave 0 (zero) if you want always be shown in every page leaved (Only for testing)\r\nRecommended value is 1"',
		'default_value' => 0,
		'wrapper'       => array(
			'width' => '50',
		),
	);

	$form_register_user_alerts = array(
		'key'          => 'field_tamarind_forms_field_register_regulatory_alerts',
		'label'        => __( 'Register user in Regulatory alerts', 'tamarind-forms' ),
		'name'         => 'register_regulatory_alerts',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_register_user_shop = array(
		'key'          => 'field_tamarind_forms_field_register_shop',
		'label'        => __( 'Register user in Shop form', 'tamarind-forms' ),
		'name'         => 'register_user_shop',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_update_user_userarea = array(
		'key'          => 'field_tamarind_forms_field_update_user_userarea',
		'label'        => __( 'Update user in User area', 'tamarind-forms' ),
		'name'         => 'update_user_userarea',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_contact_preferences_userarea = array(
		'key'          => 'field_tamarind_forms_field_contact_preferences_userarea',
		'label'        => __( 'User contact preferences in User area', 'tamarind-forms' ),
		'name'         => 'contact_preferences_userarea',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_login_user = array(
		'key'          => 'field_tamarind_forms_field_login_user',
		'label'        => __( 'Login user', 'tamarind-forms' ),
		'name'         => 'login_user',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_login_user_shop = array(
		'key'          => 'field_tamarind_forms_field_login_user_shop',
		'label'        => __( 'Login user in Shop', 'tamarind-forms' ),
		'name'         => 'login_user_shop',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_login_user_restricted = array(
		'key'          => 'field_tamarind_forms_field_login_user_restricted',
		'label'        => __( 'Login user restricted content in modal', 'tamarind-forms' ),
		'name'         => 'login_user_restricted',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_forgot_user = array(
		'key'          => 'field_tamarind_forms_field_forgot_password',
		'label'        => __( 'Forgot password', 'tamarind-forms' ),
		'name'         => 'forgot_password',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_reset_user = array(
		'key'          => 'field_tamarind_forms_field_reset_password',
		'label'        => __( 'Reset password', 'tamarind-forms' ),
		'name'         => 'reset_password',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_change_password_userarea = array(
		'key'          => 'field_tamarind_forms_field_change_password_userarea',
		'label'        => __( 'User Area: Change password', 'tamarind-forms' ),
		'name'         => 'change_password_userarea',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	$form_contact_userarea = array(
		'key'          => 'field_tamarind_forms_field_contact_userarea',
		'label'        => __( 'User Area: Contact', 'tamarind-forms' ),
		'name'         => 'contact_userarea',
		'type'         => 'clone',
		'clone'        => array(
			0 => 'group_tamarind_forms_select_form',
		),
		'display'      => 'seamless',
		'layout'       => 'block',
		'prefix_label' => 0,
		'prefix_name'  => 1,
	);

	/* TABS */

	$tab_field_type = array(
		'key'       => 'field_tamarind_forms_tab_type',
		'label'     => __( 'Forms Types', 'tamarind-forms' ),
		'name'      => 'tab_type',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$tab_field_dinamic = array(
		'key'   => 'field_tamarind_forms_tab_dinamic',
		'label' => __( 'Dinamic Fields', 'tamarind-forms' ),
		'name'  => 'tab_dinamic',
		'type'  => 'tab',
	);

	$tab_field_zoho = array(
		'key'   => 'field_tamarind_forms_tab_zoho',
		'label' => __( 'Zoho', 'tamarind-forms' ),
		'name'  => 'tab_zoho',
		'type'  => 'tab',
	);

	$tab_field_validate = array(
		'key'   => 'field_tamarind_forms_tab_validate',
		'label' => __( 'Validations', 'tamarind-forms' ),
		'name'  => 'tab_validations',
		'type'  => 'tab',
	);

	$tab_field_consent = array(
		'key'   => 'field_tamarind_forms_tab_consent',
		'label' => __( 'Consent', 'tamarind-forms' ),
		'name'  => 'tab_consent',
		'type'  => 'tab',
	);

	$tab_field_newsletters = array(
		'key'   => 'field_tamarind_forms_tab_newslettters',
		'label' => __( 'Newsletters', 'tamarind-forms' ),
		'name'  => 'tab_newsletters',
		'type'  => 'tab',
	);

	$tab_field_events = array(
		'key'   => 'field_tamarind_forms_tab_events',
		'label' => __( 'Events', 'tamarind-forms' ),
		'name'  => 'tab_events',
		'type'  => 'tab',
	);

	$tab_field_rate = array(
		'key'   => 'field_tamarind_forms_tab_rate',
		'label' => __( 'Rate', 'tamarind-forms' ),
		'name'  => 'tab_rate',
		'type'  => 'tab',
	);

	$tab_field_downloads = array(
		'key'   => 'field_tamarind_forms_tab_downloads',
		'label' => __( 'Downloads', 'tamarind-forms' ),
		'name'  => 'tab_downloads',
		'type'  => 'tab',
	);

	$tab_field_downloads_product = array(
		'key'   => 'field_tamarind_forms_tab_downloads_product',
		'label' => __( 'Products', 'tamarind-forms' ),
		'name'  => 'tab_downloads_product',
		'type'  => 'tab',
	);

	$tab_field_contact = array(
		'key'   => 'field_tamarind_forms_tab_contact',
		'label' => __( 'Contact', 'tamarind-forms' ),
		'name'  => 'tab_contact',
		'type'  => 'tab',
	);

	$tab_field_trainings = array(
		'key'   => 'field_tamarind_forms_tab_trainings',
		'label' => __( 'Trainings', 'tamarind-forms' ),
		'name'  => 'tab_trainings',
		'type'  => 'tab',
	);

	$tab_field_china = array(
		'key'   => 'field_tamarind_forms_tab_china',
		'label' => __( 'China', 'tamarind-forms' ),
		'name'  => 'tab_china',
		'type'  => 'tab',
	);

	$tab_field_users = array(
		'key'   => 'field_tamarind_forms_tab_users',
		'label' => __( 'Users', 'tamarind-forms' ),
		'name'  => 'tab_users',
		'type'  => 'tab',
	);

	$tab_field_userarea = array(
		'key'   => 'field_tamarind_forms_tab_userarea',
		'label' => __( 'User area', 'tamarind-forms' ),
		'name'  => 'tab_userarea',
		'type'  => 'tab',
	);

	/* Message fields */
	$message_field_newsletter_footer = array(
		'key'     => 'field_tamarind_forms_message_newsletter_footer',
		'label'   => __( '<h3>Config Newsletters Footer</h3>', 'tamarind-forms' ),
		'name'    => 'message_newsletter_footer',
		'type'    => 'message',
		'message' => __( 'Define <b>newsletter form </b> in footer', 'tamarind-forms' ),
	);

	$message_field_newsletter_post = array(
		'key'     => 'field_tamarind_forms_message_newsletter_post',
		'label'   => __( '<h3>Config Newsletters Post</h3>', 'tamarind-forms' ),
		'name'    => 'message_newsletter_post',
		'type'    => 'message',
		'message' => __( 'Define <b>newsletter form in post</b>', 'tamarind-forms' ),
	);

	$message_field_newsletter_events = array(
		'key'     => 'field_tamarind_forms_message_newsletter_events',
		'label'   => __( '<h3>Config Newsletters Events</h3>', 'tamarind-forms' ),
		'name'    => 'message_newsletter_events',
		'type'    => 'message',
		'message' => __( 'Define <b>newsletter events forms.</b>', 'tamarind-forms' ),
	);

	$message_field_events = array(
		'key'     => 'field_tamarind_forms_message_events',
		'label'   => __( '<h3>Config Events</h3>', 'tamarind-forms' ),
		'name'    => 'message_events',
		'type'    => 'message',
		'message' => __( 'Define info for <b>events form.</b>', 'tamarind-forms' ),
	);

	$message_field_rate = array(
		'key'     => 'field_tamarind_forms_message_rate',
		'label'   => __( '<h3>Config Rate Form</h3>', 'tamarind-forms' ),
		'name'    => 'message_rate',
		'type'    => 'message',
		'message' => __( 'Define info for <b>rate form.</b>', 'tamarind-forms' ),
	);

	$message_field_download_pdf = array(
		'key'     => 'field_tamarind_forms_message_download_pdf',
		'label'   => __( '<h3>Config Download PDF</h3>', 'tamarind-forms' ),
		'name'    => 'message_download_pdf',
		'type'    => 'message',
		'message' => __( 'Define info for <b>download PDF form.</b>', 'tamarind-forms' ),
	);

	$message_field_free_sample = array(
		'key'     => 'field_tamarind_forms_message_free_sample',
		'label'   => __( '<h3>Config Free Sample</h3>', 'tamarind-forms' ),
		'name'    => 'message_free_sample',
		'type'    => 'message',
		'message' => __( 'Define info for <b>free sample form.</b>', 'tamarind-forms' ),
	);

	$message_field_download_product = array(
		'key'     => 'field_tamarind_forms_message_download_product',
		'label'   => __( '<h3>Config Download Product</h3>', 'tamarind-forms' ),
		'name'    => 'message_download_product',
		'type'    => 'message',
		'message' => __( 'Define info for <b>download Product form.</b>', 'tamarind-forms' ),
	);

	$message_field_download_product_sidebar = array(
		'key'     => 'field_tamarind_forms_message_download_product_sidebar',
		'label'   => __( '<h3>Config Download Product Sidebar</h3>', 'tamarind-forms' ),
		'name'    => 'message_download_product_sidebar',
		'type'    => 'message',
		'message' => __( 'Define info for <b>download Product Sidebar form.</b>', 'tamarind-forms' ),
	);

	$message_field_free_report = array(
		'key'     => 'field_tamarind_forms_message_free_report',
		'label'   => __( '<h3>Config Free Report</h3>', 'tamarind-forms' ),
		'name'    => 'message_free_report',
		'type'    => 'message',
		'message' => __( 'Define info for <b>download Free Report form.</b>', 'tamarind-forms' ),
	);

	$message_field_need_help = array(
		'key'     => 'field_tamarind_forms_message_need_help',
		'label'   => __( '<h3>Config Need Help</h3>', 'tamarind-forms' ),
		'name'    => 'message_need_help',
		'type'    => 'message',
		'message' => __( 'Define info for <b>Need help form.</b>', 'tamarind-forms' ),
	);

	$message_field_contact = array(
		'key'     => 'field_tamarind_forms_message_contact',
		'label'   => __( '<h3>Config Contact</h3>', 'tamarind-forms' ),
		'name'    => 'message_contact',
		'type'    => 'message',
		'message' => __( 'Define info for <b>contact form.</b>', 'tamarind-forms' ),
	);

	$message_field_chat = array(
		'key'     => 'field_tamarind_forms_message_chat',
		'label'   => __( '<h3>Config Chat</h3>', 'tamarind-forms' ),
		'name'    => 'message_chat',
		'type'    => 'message',
		'message' => __( 'Define info for <b>chat form.</b>', 'tamarind-forms' ),
	);

	$message_field_trainings = array(
		'key'     => 'field_tamarind_forms_message_trainings',
		'label'   => __( '<h3>Config Trainings</h3>', 'tamarind-forms' ),
		'name'    => 'message_trainings',
		'type'    => 'message',
		'message' => __( 'Define info for <b>trainings form.</b>', 'tamarind-forms' ),
	);

	$message_field_china_labels = array(
		'key'     => 'field_tamarind_forms_message_china_labels',
		'label'   => __( '<h3>Config China Labels</h3>', 'tamarind-forms' ),
		'name'    => 'message_china_labels',
		'type'    => 'message',
		'message' => __( 'Define labels for <b>china form.</b>', 'tamarind-forms' ),
	);

	$message_field_china = array(
		'key'     => 'field_tamarind_forms_message_china',
		'label'   => __( '<h3>Config China</h3>', 'tamarind-forms' ),
		'name'    => 'message_china',
		'type'    => 'message',
		'message' => __( 'Define info for <b>china form.</b>', 'tamarind-forms' ),
	);

	$message_field_users_labels = array(
		'key'     => 'field_tamarind_forms_message_users_labels',
		'label'   => __( '<h3>Config Users Forms</h3>', 'tamarind-forms' ),
		'name'    => 'message_users_labels',
		'type'    => 'message',
		'message' => __( 'Define forms for <b>users.</b>', 'tamarind-forms' ),
	);

	$message_field_register_user_onexit = array(
		'key'     => 'field_tamarind_forms_message_register_onexit',
		'label'   => __( '<h3>Config Register on exit in Modal</h3>', 'tamarind-forms' ),
		'name'    => 'message_register_onexit',
		'type'    => 'message',
		'message' => __( 'Define info for <b>register onexit form.</b>', 'tamarind-forms' ),
	);

	$message_field_register_user_alerts = array(
		'key'     => 'field_tamarind_forms_message_register',
		'label'   => __( '<h3>Config Register user in Regulatory alerts</h3>', 'tamarind-forms' ),
		'name'    => 'message_register',
		'type'    => 'message',
		'message' => __( 'Define info for <b>register user form.</b>', 'tamarind-forms' ),
	);

	$message_field_register_user_shop = array(
		'key'     => 'field_tamarind_forms_message_register_shop',
		'label'   => __( '<h3>Config Register user in Shop</h3>', 'tamarind-forms' ),
		'name'    => 'message_register_shop',
		'type'    => 'message',
		'message' => __( 'Define info for <b>register user shop form.</b>', 'tamarind-forms' ),
	);

	$message_field_update_user_userarea = array(
		'key'     => 'field_tamarind_forms_message_update_user_userarea',
		'label'   => __( '<h3>Config Update user in User Area</h3>', 'tamarind-forms' ),
		'name'    => 'message_update_user_userarea',
		'type'    => 'message',
		'message' => __( 'Define info for <b>update user form.</b>', 'tamarind-forms' ),
	);

	$message_field_contact_preferences_userarea = array(
		'key'     => 'field_tamarind_forms_message_contact_preferences_userarea',
		'label'   => __( '<h3>Config User contact preferences in User Area</h3>', 'tamarind-forms' ),
		'name'    => 'message_contact_preferences_userarea',
		'type'    => 'message',
		'message' => __( 'Define info for <b>User contact preferences form.</b>', 'tamarind-forms' ),
	);

	$message_field_login_user = array(
		'key'     => 'field_tamarind_forms_message_login',
		'label'   => __( '<h3>Config Login</h3>', 'tamarind-forms' ),
		'name'    => 'message_login',
		'type'    => 'message',
		'message' => __( 'Define info for <b>login form.</b>', 'tamarind-forms' ),
	);

	$message_field_login_user_shop = array(
		'key'     => 'field_tamarind_forms_message_login_shop',
		'label'   => __( '<h3>Config Login Shop</h3>', 'tamarind-forms' ),
		'name'    => 'message_login_shop',
		'type'    => 'message',
		'message' => __( 'Define info for <b>login shop form.</b>', 'tamarind-forms' ),
	);

	$message_field_login_user_restricted = array(
		'key'     => 'field_tamarind_forms_message_login_restricted',
		'label'   => __( '<h3>Config Login restricted content</h3>', 'tamarind-forms' ),
		'name'    => 'message_login_restricted',
		'type'    => 'message',
		'message' => __( 'Define info for <b>login restricted content modal.</b>', 'tamarind-forms' ),
	);

	$message_field_forgot_user = array(
		'key'     => 'field_tamarind_forms_message_forgot',
		'label'   => __( '<h3>Config Forgot Password</h3>', 'tamarind-forms' ),
		'name'    => 'message_forgot',
		'type'    => 'message',
		'message' => __( 'Define info for <b>forgot password form.</b>', 'tamarind-forms' ),
	);

	$message_field_reset_user = array(
		'key'     => 'field_tamarind_forms_message_reset',
		'label'   => __( '<h3>Config Reset Password</h3>', 'tamarind-forms' ),
		'name'    => 'message_reset',
		'type'    => 'message',
		'message' => __( 'Define info for <b>reset password form.</b>', 'tamarind-forms' ),
	);

	$message_field_change_password = array(
		'key'     => 'field_tamarind_forms_message_change',
		'label'   => __( '<h3>Config Change Password in User Area</h3>', 'tamarind-forms' ),
		'name'    => 'message_change',
		'type'    => 'message',
		'message' => __( 'Define info for <b>change password form.</b>', 'tamarind-forms' ),
	);

	$message_field_contact_userarea = array(
		'key'     => 'field_tamarind_forms_message_contact_userarea',
		'label'   => __( '<h3>Config Contact in User Area</h3>', 'tamarind-forms' ),
		'name'    => 'message_contact_userarea',
		'type'    => 'message',
		'message' => __( 'Define info for <b>Contact form</b> in Support Centre.', 'tamarind-forms' ),
	);

	// ACF for Tamarind Forms.
	acf_add_local_field_group(
		array(
			'key'      => 'group_tamarind_forms_options',
			'title'    => __( 'Tamarind Fields for Forms', 'tamarind-forms' ),
			'fields'   => array(
				$tab_field_type,
				$form_field_define_actions_forms,
				$tab_field_dinamic,
				$forms_dinamic_fields,
				$tab_field_zoho,
				$form_zoho_platform_field,
				$form_zoho_submissions_field,
				$form_zoho_api_url_field,
				$form_zoho_api_key_field,
				$forms_zoho_action_fields,
				$tab_field_validate,
				$form_validate_platform_field,
				$forms_validate_action_fields,
				$tab_field_consent,
				$form_consent_default_field,
				$form_consent_newsletter_field,
				$tab_field_newsletters,
				$message_field_newsletter_footer,
				$form_newsletter_footer_field,
				$message_field_newsletter_post,
				$form_newsletter_post_field,
				$message_field_newsletter_events,
				$form_newsletter_events_field,
				$tab_field_events,
				$message_field_events,
				$form_events_field,
				$tab_field_rate,
				$message_field_rate,
				$form_rate_field,
				$tab_field_downloads,
				$message_field_free_report,
				$form_download_free_report_field,
				$message_field_download_pdf,
				$form_download_pdf_field,
				$message_field_free_sample,
				$form_free_sample_field,
				$tab_field_downloads_product,
				$message_field_download_product,
				$form_download_product_field,
				$message_field_download_product_sidebar,
				$form_download_product_sidebar_field,
				$message_field_need_help,
				$form_need_help_field,
				$tab_field_contact,
				$message_field_contact,
				$form_contact_field,
				$message_field_chat,
				$form_chat_field,
				$tab_field_trainings,
				$message_field_trainings,
				$form_trainings_field,
				$tab_field_china,
				$message_field_china_labels,
				$form_china_label_email_field,
				$form_china_label_company_field,
				$form_china_label_first_name_field,
				$form_china_label_last_name_field,
				$form_china_label_department_field,
				$form_china_label_industry_field,
				$form_china_label_telephone_field,
				$form_china_label_content_field,
				$form_china_label_download_button_field,
				$form_china_label_acceptance_field,
				$message_field_china,
				$form_china_field,
				$tab_field_users,
				$message_field_users_labels,
				$form_key_register_page,
				$form_user_label_register_page,
				$form_user_label_login_page,
				$form_user_label_forgot_password_page,
				$form_user_label_reset_password_page,
				$form_user_label_enable_pre_checkout,
				$form_user_label_pre_checkout_page,
				$message_field_register_user_onexit,
				$form_register_onexit,
				$activate_register_onexit,
				$cookie_register_onexit,
				$message_field_register_user_alerts,
				$form_register_user_alerts,
				$message_field_register_user_shop,
				$form_register_user_shop,
				$message_field_login_user,
				$form_login_user,
				$message_field_login_user_shop,
				$form_login_user_shop,
				$message_field_login_user_restricted,
				$form_login_user_restricted,
				$message_field_forgot_user,
				$form_forgot_user,
				$message_field_reset_user,
				$form_reset_user,
				$tab_field_userarea,
				$message_field_update_user_userarea,
				$form_update_user_userarea,
				$message_field_contact_preferences_userarea,
				$form_contact_preferences_userarea,
				$message_field_change_password,
				$form_change_password_userarea,
				$message_field_contact_userarea,
				$form_contact_userarea,
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tamarind-acf-forms-options',
					),
				),
			),
			'style'    => 'default',
		)
	);

	// ACF to SELECT/ASSIGN FORMS in content.
	$assign_form = array(
		'key'     => 'field_tamarind_forms_select_form',
		'label'   => __( 'Form', 'tamarind-forms' ),
		'name'    => 'select_form',
		'type'    => 'select',
		'choices' => array(), // Populate with forms.
		'wrapper' => array(
			'width' => '25',
		),
	);

	$assign_type_form = array(
		'key'     => 'field_tamarind_forms_select_type_form',
		'label'   => __( 'Type Form', 'tamarind-forms' ),
		'name'    => 'select_type_form',
		'type'    => 'select',
		'choices' => array(), // Populate with forms.
		'wrapper' => array(
			'width' => '25',
		),
	);

	$assign_style_form = array(
		'key'     => 'field_tamarind_forms_select_style_form',
		'label'   => __( 'Style Form', 'tamarind-forms' ),
		'name'    => 'select_style_form',
		'type'    => 'select',
		'choices' => array(
			'default'        => 'Default',
			'default-column' => 'Default in column',
			'minimal'        => 'Minimal',
			'border-box'     => 'Border Box',
		),
		'wrapper' => array(
			'width' => '25',
		),
	);

	$assign_zoho_action_form = array(
		'key'     => 'field_tamarind_forms_select_zoho_action_form',
		'label'   => __( 'Zoho Action', 'tamarind-forms' ),
		'name'    => 'select_zoho_action_form',
		'type'    => 'select',
		'choices' => array(), // Populate with forms.
		'wrapper' => array(
			'width' => '25',
		),
	);

	$assign_show_title_and_description = array(
		'key'           => 'field_tamarind_forms_select_show_title_and_description',
		'label'         => __( 'Show Title and Description', 'tamarind-forms' ),
		'name'          => 'select_show_title_and_description',
		'type'          => 'true_false',
		'style'         => 'default',
		'default_value' => 1,
		'ui'            => 1,
		'ui_on_text'    => 'Yes',
		'ui_off_text'   => 'No',
		'wrapper'       => array(
			'width' => '25',
		),
	);

	$assign_show_title_post = array(
		'key'           => 'field_tamarind_forms_select_show_title_post',
		'label'         => __( 'Show Title Post', 'tamarind-forms' ),
		'name'          => 'select_show_title_post',
		'type'          => 'true_false',
		'style'         => 'default',
		'default_value' => 0,
		'ui'            => 1,
		'ui_on_text'    => 'Yes',
		'ui_off_text'   => 'No',
		'wrapper'       => array(
			'width' => '75',
		),
	);

	$assign_title_form = array(
		'key'     => 'field_tamarind_forms_select_title_form',
		'label'   => __( 'Title Form', 'tamarind-forms' ),
		'name'    => 'select_title_form',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25',
		),
	);

	$assign_description_form = array(
		'key'     => 'field_tamarind_forms_select_description_form',
		'label'   => __( 'Description Form', 'tamarind-forms' ),
		'name'    => 'select_description_form',
		'type'    => 'textarea',
		'wrapper' => array(
			'width' => '50',
		),
		'rows'    => 2,
	);

	$assign_submit_form = array(
		'key'     => 'field_tamarind_forms_select_submit_form',
		'label'   => __( 'Submit Text', 'tamarind-forms' ),
		'name'    => 'select_submit_form',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25',
		),
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_tamarind_forms_select_form',
			'title'    => __( 'Select Form', 'tamarind-forms' ),
			'fields'   => array(
				$assign_form,
				$assign_type_form,
				$assign_style_form,
				$assign_zoho_action_form,
				$assign_show_title_and_description,
				$assign_show_title_post,
				$assign_title_form,
				$assign_description_form,
				$assign_submit_form,
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
				),
			),
			'style'    => 'block',
			'active'   => false,
		)
	);
}
