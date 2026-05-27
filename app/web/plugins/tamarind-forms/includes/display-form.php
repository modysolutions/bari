<?php
/**
 * Display Form Gravity Forms for Tamarind Forms.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\display_form;

defined( 'ABSPATH' ) || exit;

/**
 * Get the registry of dynamic Gravity Forms callbacks used during render.
 *
 * @return array<string, array<int, callable>>
 */
function &get_form_filter_registry() {
	static $registry = array(
		'title_description' => array(),
		'button'            => array(),
		'style'             => array(),
		'china_placeholder' => array(),
	);

	return $registry;
}


/**
 * Display Gravity Form.
 *
 * @param int   $id Form ID.
 * @param array $params Form parameters.
 * @param array $texts Texts for form.
 * @param bool  $title_on Title on.
 * @param bool  $description_on Description on.
 *
 * @return bool
 */
function display_gravity_form( $id, $params = array(), $texts = array(), $title_on = true, $description_on = true ) {
	$display_inactive = false;
	$ajax             = true;

	$custom_title = $texts['title'];

	$custom_description = $texts['show-title-post'] ? '<strong>' . $texts['post-title'] . '</strong>' : $texts['description'];

	if ( isset( $texts['show-title-desc'] ) && '' === $texts['show-title-desc'] ) {
		$custom_title       = '';
		$custom_description = '';
	}

	$custom_button_text = $texts['submit'];

	if ( '' === $custom_title ) {
		$title_on = false;
	}

	if ( '' === $custom_description ) {
		$description_on = false;
	}

	// Update the form's title and description.
	update_render_title_and_description_form( $id, $custom_title, $custom_description );

	// Replace the button text.
	if ( ! empty( $custom_button_text ) ) {
		update_render_button_form( $id, $custom_button_text );
	}

	$form = \GFAPI::get_form( $id );
	if ( \tamarind_forms\dinamic_fields\is_china_form( $form ) ) {
		update_render_placeholder_china( $id, $texts );
	}

	if ( method_exists( '\\GFFormDisplay', 'flush_cached_forms' ) ) {
		\GFFormDisplay::flush_cached_forms( $id . '_form_display' );
	}

	// Check if the session variable is set and display the confirmation message.
	if ( session_status() === PHP_SESSION_NONE ) {
		session_start();
	}
	// Show confirmation message with GET parameter 'send'.
	$enviado = isset( $_GET['send'] ) ? sanitize_text_field( $_GET['send'] ) : null;
	if ( '1' === $enviado && ! empty( $_SESSION['gf_confirmation'] ) ) {
		echo '<div class="box-info">' . wp_kses_post( $_SESSION['gf_confirmation'] ) . '</div>';
		unset( $_SESSION['gf_confirmation'] ); // Clear the message to avoid duplicates.
	}

	gravity_form( $id, $title_on, $description_on, $display_inactive, $params, $ajax, 1 );

	// Remove filters.
	remove_render_title_and_description_form( $id );
	if ( ! empty( $custom_button_text ) ) {
		remove_render_button_form( $id );
	}
	if ( \tamarind_forms\dinamic_fields\is_china_form( $form ) ) {
		remove_render_placeholder_china( $id );
	}

	return true;
}

/**
 * Update Render Title and Description Form.
 *
 * @param int    $form_id Form ID.
 * @param string $custom_title Custom Title.
 * @param string $custom_description Custom Description.
 *
 * @return void
 */
function update_render_title_and_description_form( $form_id, $custom_title, $custom_description ) {
	$registry = &get_form_filter_registry();

	if ( isset( $registry['title_description'][ $form_id ] ) ) {
		remove_filter( 'gform_pre_render_' . $form_id, $registry['title_description'][ $form_id ], 10 );
	}

	$registry['title_description'][ $form_id ] = function ( $form ) use ( $custom_title, $custom_description ) {
		if ( ! empty( $custom_title ) ) {
			$form['title'] = $custom_title;
		}
		if ( ! empty( $custom_description ) ) {
			$form['description'] = $custom_description;
		}
		return $form;
	};

	add_filter( 'gform_pre_render_' . $form_id, $registry['title_description'][ $form_id ], 10 );
}

/**
 * Remove render title and description filter.
 *
 * @param int $form_id Form ID.
 *
 * @return void
 */
function remove_render_title_and_description_form( $form_id ) {
	$registry = &get_form_filter_registry();

	if ( ! isset( $registry['title_description'][ $form_id ] ) ) {
		return;
	}

	remove_filter( 'gform_pre_render_' . $form_id, $registry['title_description'][ $form_id ], 10 );
	unset( $registry['title_description'][ $form_id ] );
}

/**
 * Update Render Button Form.
 *
 * @param int    $form_id Form ID.
 * @param string $custom_button_text Custom Button Text.
 *
 * @return void
 */
function update_render_button_form( $form_id, $custom_button_text ) {
	$registry = &get_form_filter_registry();

	if ( isset( $registry['button'][ $form_id ] ) ) {
		remove_filter( 'gform_submit_button_' . $form_id, $registry['button'][ $form_id ], 10 );
	}

	$registry['button'][ $form_id ] = function ( $button ) use ( $custom_button_text ) {
		return preg_replace_callback(
			'/(<input[^>]+type=["\']submit["\'][^>]+value=["\'])[^"\']*(["\'])/i',
			function ( $matches ) use ( $custom_button_text ) {
				return $matches[1] . esc_attr( $custom_button_text ) . $matches[2];
			},
			$button
		) ?? $button;
	};

	add_filter( 'gform_submit_button_' . $form_id, $registry['button'][ $form_id ], 10, 2 );
}

/**
 * Remove render button filter.
 *
 * @param int $form_id Form ID.
 *
 * @return void
 */
function remove_render_button_form( $form_id ) {
	$registry = &get_form_filter_registry();

	if ( ! isset( $registry['button'][ $form_id ] ) ) {
		return;
	}

	remove_filter( 'gform_submit_button_' . $form_id, $registry['button'][ $form_id ], 10 );
	unset( $registry['button'][ $form_id ] );
}

/**
 * Update Render Placeholder for China Form.
 *
 * @param int   $form_id Form ID.
 * @param array $texts   Texts for placeholders.
 *
 * @return void
 */
function update_render_placeholder_china( $form_id, $texts ) {
	$registry = &get_form_filter_registry();

	if ( isset( $registry['china_placeholder'][ $form_id ] ) ) {
		remove_filter( 'gform_field_content', $registry['china_placeholder'][ $form_id ], 10 );
	}

	$registry['china_placeholder'][ $form_id ] = function ( $field_content, $field ) use ( $texts ) {

			switch ( $field->type ) {
				case 'email':
					$field_content = str_replace( 'placeholder=\'' . $field->placeholder . '\'', 'placeholder=\'' . ( $texts['label-email'] ?? '' ) . '\'', $field_content );
					break;
				case 'text':
					if ( 'Company Name' === $field->label ) {
						$field_content = str_replace( 'placeholder=\'' . $field->placeholder . '\'', 'placeholder=\'' . ( $texts['label-company'] ?? '' ) . '\'', $field_content );
					}
					break;
				case 'select':
					if ( 'Department' === $field->label ) {
						$field_content = str_replace( 'placeholder=\'Select a Department\'', 'placeholder=\'' . ( $texts['label-department'] ?? '' ) . '\'', $field_content );
					}
					if ( 'Industry' === $field->label ) {
						$field_content = str_replace( 'placeholder=\'Select a Industry\'', 'placeholder=\'' . ( $texts['label-industry'] ?? '' ) . '\'', $field_content );
					}
					break;
				case 'name':
					$field_content = str_replace( 'placeholder=\'First Name\'', 'placeholder=\'' . ( $texts['label-first-name'] ?? '' ) . '\'', $field_content );
					$field_content = str_replace( 'placeholder=\'Last Name\'', 'placeholder=\'' . ( $texts['label-last-name'] ?? '' ) . '\'', $field_content );
					break;
				case 'phone':
					$field_content = str_replace( 'placeholder=\'' . $field->placeholder . '\'', 'placeholder=\'' . ( $texts['label-telephone'] ?? '' ) . '\'', $field_content );
					break;
				case 'textarea':
					$field_content = str_replace( 'textarea', 'textarea placeholder="' . ( $texts['label-content'] ?? '' ) . '"', $field_content );
					break;
				case 'consent':
					$new_placeholder = ( $texts['label-consent'] ?? '' );
					break;
				default:
					break;
			}
			return $field_content;
	};

	add_filter( 'gform_field_content', $registry['china_placeholder'][ $form_id ], 10, 5 );
}

/**
 * Remove China placeholder filter.
 *
 * @param int $form_id Form ID.
 *
 * @return void
 */
function remove_render_placeholder_china( $form_id ) {
	$registry = &get_form_filter_registry();

	if ( ! isset( $registry['china_placeholder'][ $form_id ] ) ) {
		return;
	}

	remove_filter( 'gform_field_content', $registry['china_placeholder'][ $form_id ], 10 );
	unset( $registry['china_placeholder'][ $form_id ] );
}

/**
 * Get Form Default Options.
 * Get the default options for the form from the ACF settings options.
 *
 * @param string $type_form    Type Form.
 * @param string $option_text  Option Text.
 *
 * @return string
 */
function get_form_default_options( $type_form, $option_text ) {

	$text       = '';
	$type_forms = get_field( 'define_actions_forms', 'option' );
	if ( $type_forms ) {
		foreach ( $type_forms as $type ) {
			if ( $type['default'] && $text == '' ) {
				$text = $type[ $option_text ];
			}
			if ( $type_form === $type['name'] && '' !== $type[ $option_text ] ) {
				$text = $type[ $option_text ];
			}
		}
	}
	return $text;
}

/** STYLE IN FORMS - CLASS: STYLE **/

/**
 * Add Class Style Form.
 *
 * @param string $form_id    Form ID.
 * @param string $style_form Style Form.
 *
 * @return void
 */
function add_class_style_form( $form_id, $style_form ) {
	$registry = &get_form_filter_registry();

	if ( isset( $registry['style'][ $form_id ] ) ) {
		remove_filter( 'gform_pre_render_' . $form_id, $registry['style'][ $form_id ], 10 );
	}

	$registry['style'][ $form_id ] = function ( $form ) use ( $style_form ) {
		$form['cssClass'] = 'tm-form tm-form-style-' . $style_form;
		return $form;
	};
	add_filter( 'gform_pre_render_' . $form_id, $registry['style'][ $form_id ], 10, 4 );
}

/**
 * Remove Class Style Form.
 *
 * @param string $form_id    Form ID.
 *
 * @return void
 */
function remove_class_style_form( $form_id ) {
	$registry = &get_form_filter_registry();

	if ( ! isset( $registry['style'][ $form_id ] ) ) {
		return;
	}

	remove_filter( 'gform_pre_render_' . $form_id, $registry['style'][ $form_id ], 10 );
	unset( $registry['style'][ $form_id ] );
}

/**
 * Get a parameter value from an array.
 *
 * @param array  $params Array of parameters.
 * @param string $key    Key to retrieve the value for.
 *
 * @return mixed The value of the parameter if it exists, or an empty string.
 */
function get_param_value( $params, $key ) {
	if ( isset( $params[ $key ] ) ) {
		return $params[ $key ];
	}
	return '';
}

/**
 * Pre Display.
 *
 * @param int    $form_id    Form ID.
 * @param array  $params     Array of parameters.
 * @param string $style_form Style Form.
 *
 * @return void
 */
function pre_display( $form_id, $params, $style_form ) {

	add_filter_hidden_fields(
		array(
			'zoho-platform'    => get_param_value( $params, 'zoho-platform' ),
			'zoho-input-name'  => get_param_value( $params, 'zoho-input-name' ),
			'zoho-lead-action' => get_param_value( $params, 'zoho-lead-action' ),
			'type-form'        => get_param_value( $params, 'type-form' ),
			'file'             => get_param_value( $params, 'file' ),
			'country-code'     => get_param_value( $params, 'country-telephone' ),
			'event-name'       => get_param_value( $params, 'event-name' ),
			'submit-key'       => get_param_value( $params, 'submit-key' ),
		)
	);

	$form = \GFAPI::get_form( $form_id );
	if ( \tamarind_forms\dinamic_fields\is_china_form( $form ) ) {
		$consent_text = get_field( 'field_tamarind_forms_field_china_label_acceptance', 'option' );
	} else {
		$consent_text = get_param_value( $params, 'consent' );
	}

	add_filter_consent_fields(
		array(
			'consent' => $consent_text,
		)
	);

	add_class_style_form( $form_id, $style_form );
}

/**
 * Post Display.
 *
 * @param int $form_id Form ID.
 *
 * @return void
 */
function post_display( $form_id ) {
	remove_class_style_form( $form_id );
	// Reset filters.
	reset_filter_consent_fields();
	reset_filter_hidden_fields();
}

/**
 * Get Country Telephone. True if the form has a phone field. False if not.
 *
 * @param int $form_id Form ID.
 *
 * @return bool
 */
function get_country_telephone( $form_id ) {
	$country_telephone = false;
	$form              = \GFAPI::get_form( $form_id );
	foreach ( $form['fields'] as $field ) {
		if ( 'phone' === $field->type ) {
			$country_telephone = true;
		}
	}
	return $country_telephone;
}

/**
 * Get Consent Message.
 *
 * @param string $type_form Type Form.
 *
 * @return string
 */
function get_consent_message( $type_form ) {
	$consent_message = get_field( 'default_consent', 'option' );
	if ( 'newsletter' === $type_form || 'newsletter-events' === $type_form ) {
		$consent_message = get_field( 'newsletter_consent', 'option' );
	}
	return $consent_message;
}

/**
 * Get Input Name for Zoho. Title of the page or post.
 *
 * @return string
 */
function get_input_name() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_page() || is_single() ) {
		return get_the_title();
	}
	if ( is_post_type_archive( 'regulatory_alert' ) ) {
		return get_regulatory_alert_title();
	}
	return '';
}

/**
 * Get the regulatory alert title.
 *
 * This function retrieves the title for regulatory alerts based on query parameters
 * and taxonomy terms. It handles various cases such as geography and content types.
 *
 * @return string The regulatory alert title.
 */
function get_regulatory_alert_title() {

	if ( isset( $_GET['geography'] ) ) {
		$geoslug = sanitize_text_field( wp_unslash( $_GET['geography'] ) );
	} else {
		$geoslug = '';
	}

	if ( isset( $_GET['content_types'] ) ) {
		$otherslug = sanitize_text_field( wp_unslash( $_GET['content_types'] ) );
	} else {
		$otherslug = '';
	}

	$geoslug = get_term_by( 'slug', $geoslug, 'geography' );
	if ( $geoslug && ! empty( $geoslug ) ) {
		$geoname = $geoslug->name;
	} else {
		$geoname = '';
	}

	$otherslug = get_term_by( 'slug', $otherslug, 'content_types' );

	if ( $otherslug && ! empty( $otherslug ) ) {
		$othername = $otherslug->name;
	} else {
		$othername = '';
	}

	$el_term = get_queried_object();

	$pre_title = '';
	if ( isset( $el_term->taxonomy ) ) {
		$pre_title = $el_term->taxonomy;
		$pre_title = str_replace( 'post_tag', 'TobaccoIntelligence - complete coverage of', $pre_title );
		$pre_title = str_replace( 'topics', '', $pre_title );
		$pre_title = str_replace( 'Topics', '', $pre_title );
		$pre_title = ucfirst( $pre_title ) . ' ';
	}

	if ( '' === $geoslug || '' === $otherslug ) {
		$pre_title = get_field( 'alerts_title_landing', 'options' );
	} else {
		$titulo_cat = $othername . ' / ' . $geoname;
		return $titulo_cat;
	}

	if ( isset( $_GET['geoalerts'] ) ) {
		$slug = $_GET['geoalerts'];
		$term = get_term_by( 'slug', $slug, 'geography' );

		if ( '' !== $slug ) {
			if ( 'americas' === $slug ) {
				$term_name = 'AMERICAS';
			} else {
				$term_name = strtoupper( $term->name );
			}
			return $pre_title . ' / ' . $term_name;
		}
	}

	return get_field( 'alerts_title_landing', 'options' );
}


/**
 * Display Form.
 *
 * @param string $name_field Name Field.
 * @param bool   $options    Options.
 * @param int    $post_id    Post ID.
 * @param string $downloadable_file Downloadable File.
 *
 * @return bool
 */
function display_form( $name_field = 'select_form', $options = false, $post_id = 0, $downloadable_file = '' ) {

	if ( get_field( $name_field ) || $options ) {
		if ( ! $options ) {
			$form_id                 = get_field( $name_field . '_select_form' );
			$type_form               = get_field( $name_field . '_select_type_form' );
			$style_form              = get_field( $name_field . '_select_style_form' );
			$select_zoho_action_form = get_field( $name_field . '_select_zoho_action_form' );

			$select_title_form = get_field( $name_field . '_select_title_form' ) ? get_field( $name_field . '_select_title_form' ) : get_form_default_options( $type_form, 'type_title' );

			$select_description_form = get_field( $name_field . '_select_description_form' ) ? get_field( $name_field . '_select_description_form' ) : get_form_default_options( $type_form, 'type_description' );

			$select_submit_form = get_field( $name_field . '_select_submit_form' ) ? get_field( $name_field . '_select_submit_form' ) : get_form_default_options( $type_form, 'type_submit_text' );

			$select_show_title_and_description = get_field( $name_field . '_select_show_title_and_description' ) ? get_field( $name_field . '_select_show_title_and_description' ) : '';
			$select_show_title_post            = get_field( $name_field . '_select_show_title_post' ) ? get_field( $name_field . '_select_show_title_post' ) : '';
		} else {
			// They are located in the theme options (ACF).
			$options_form = get_field( $name_field, 'option' );

			$form_id                 = $options_form['select_form'];
			$type_form               = $options_form['select_type_form'];
			$style_form              = $options_form['select_style_form'];
			$select_zoho_action_form = $options_form['select_zoho_action_form'];

			$select_title_form = ( '' !== $options_form['select_title_form'] && isset( $options_form['select_title_form'] ) ) ? $options_form['select_title_form'] : get_form_default_options( $type_form, 'type_title' );

			$select_description_form = ( '' !== $options_form['select_description_form'] && isset( $options_form['select_description_form'] ) ) ? $options_form['select_description_form'] : get_form_default_options( $type_form, 'type_description' );

			$select_submit_form = ( '' !== $options_form['select_submit_form'] && isset( $options_form['select_submit_form'] ) ) ? $options_form['select_submit_form'] : get_form_default_options( $type_form, 'type_submit_text' );

			$select_show_title_and_description = ( isset( $options_form['select_show_title_and_description'] ) && '' !== $options_form['select_show_title_and_description'] ) ? $options_form['select_show_title_and_description'] : '';

			$select_show_title_post = ( isset( $options_form['select_show_title_post'] ) && '' !== $options_form['select_show_title_post'] ) ? $options_form['select_show_title_post'] : '';
		}

		// If Form is not selected.
		if ( '' === $form_id ) {
			return false;
		}

		$option_zoho_platform = get_field( 'platform', 'option' );
		$option_input_name    = get_input_name();

		$params = array(
			'type-form'         => $type_form,
			'zoho-platform'     => $option_zoho_platform,
			'zoho-lead-action'  => $select_zoho_action_form,
			'zoho-input-name'   => $option_input_name,
			'file'              => $downloadable_file,
			'country-telephone' => get_country_telephone( $form_id ),
			'consent'           => get_consent_message( $type_form ),
			'submit-key'        => get_permalink(),
		);

		$param_text = array(
			'title'           => $select_title_form,
			'description'     => $select_description_form,
			'submit'          => $select_submit_form,
			'post-title'      => ( 0 !== $post_id ) ? get_the_title( $post_id ) : '',
			'show-title-desc' => $select_show_title_and_description,
			'show-title-post' => $select_show_title_post,
		);

		if ( ( 'china' === $type_form ) || ( 'china-download' === $type_form ) ) {
			$param_text['label-email']      = get_field( 'field_tamarind_forms_field_china_label_email', 'option' );
			$param_text['label-company']    = get_field( 'field_tamarind_forms_field_china_label_company', 'option' );
			$param_text['label-first-name'] = get_field( 'field_tamarind_forms_field_china_label_first_name', 'option' );
			$param_text['label-last-name']  = get_field( 'field_tamarind_forms_field_china_label_last_name', 'option' );
			$param_text['label-department'] = get_field( 'field_tamarind_forms_field_china_label_department', 'option' );
			$param_text['label-industry']   = get_field( 'field_tamarind_forms_field_china_label_industry', 'option' );
			$param_text['label-telephone']  = get_field( 'field_tamarind_forms_field_china_label_telephone', 'option' );
			$param_text['label-content']    = get_field( 'field_tamarind_forms_field_china_label_content', 'option' );
		}

		pre_display( $form_id, $params, $style_form );
		display_gravity_form( $form_id, $params, $param_text );
		post_display( $form_id, $params, $style_form );

		return true;
	}
	return false;
}

/**
 * Display Module Form.
 *
 * @param array  $params            Parameters.
 * @param string $downloadable_file Downloadable File.
 *
 * @return bool
 */
function display_module_form( $params, $downloadable_file = '' ) {

	$form_id = $params['form_id'];

	if ( '' === $form_id ) {
		return false;
	}

	$type_form               = $params['type_form'];
	$style_form              = $params['style_form'];
	$select_zoho_action_form = $params['select_zoho_action_form'];

$select_title_form       = isset( $params['title_form'] ) && ! empty( $params['title_form'] ) ? $params['title_form'] : get_form_default_options( $type_form, 'type_title' );
        $select_description_form = isset( $params['description_form'] ) && ! empty( $params['description_form'] ) ? $params['description_form'] : get_form_default_options( $type_form, 'type_description' );
        $select_submit_form      = isset( $params['submit_form'] ) && ! empty( $params['submit_form'] ) ? $params['submit_form'] : get_form_default_options( $type_form, 'type_submit_text' );

	$select_show_title_and_description = isset( $params['select_show_title_and_description'] ) && '' !== $params['select_show_title_and_description'] ? $params['select_show_title_and_description'] : '';
	$select_show_title_post            = isset( $params['select_show_title_post'] ) ? $params['select_show_title_post'] : '';

	$option_zoho_platform = get_field( 'platform', 'option' );
	$option_input_name    = get_input_name();

	$params = array(
		'type-form'         => $type_form,
		'zoho-platform'     => $option_zoho_platform,
		'zoho-lead-action'  => $select_zoho_action_form,
		'zoho-input-name'   => $option_input_name,
		'file'              => $downloadable_file,
		'country-telephone' => get_country_telephone( $form_id ),
		'consent'           => get_consent_message( $type_form ),
		'submit-key'        => get_permalink(),
	);

	$param_text = array(
		'title'           => $select_title_form,
		'description'     => $select_description_form,
		'submit'          => $select_submit_form,
		'post-title'      => $select_description_form,
		'show-title-desc' => $select_show_title_and_description,
		'show-title-post' => $select_show_title_post,
	);

	if ( ( 'china' === $type_form ) || ( 'china-download' === $type_form ) ) {
		$param_text['label-email']      = get_field( 'field_tamarind_forms_field_china_label_email', 'option' );
		$param_text['label-company']    = get_field( 'field_tamarind_forms_field_china_label_company', 'option' );
		$param_text['label-first-name'] = get_field( 'field_tamarind_forms_field_china_label_first_name', 'option' );
		$param_text['label-last-name']  = get_field( 'field_tamarind_forms_field_china_label_last_name', 'option' );
		$param_text['label-department'] = get_field( 'field_tamarind_forms_field_china_label_department', 'option' );
		$param_text['label-industry']   = get_field( 'field_tamarind_forms_field_china_label_industry', 'option' );
		$param_text['label-telephone']  = get_field( 'field_tamarind_forms_field_china_label_telephone', 'option' );
		$param_text['label-content']    = get_field( 'field_tamarind_forms_field_china_label_content', 'option' );
	}

	pre_display( $form_id, $params, $style_form );
	display_gravity_form( $form_id, $params, $param_text );
	post_display( $form_id, $params, $style_form );

	return true;
}

/**
 * Display Download Form.
 *
 * @param string $format           Format.
 * @param int    $post_id          Post ID.
 * @param bool   $define_in_options Whether to define in options.
 *
 * @return void
 */
function display_download_form( $format, $post_id = 0, $define_in_options = true ) {

	$downloadable_file = get_download( $format, $post_id );

	if ( $downloadable_file && '' !== $downloadable_file ) {
		display_form( $format, $define_in_options, $post_id, $downloadable_file );
	}
}

/**
 * Get Download.
 *
 * @param string $format  Format.
 * @param int    $post_id Post ID.
 *
 * @return string
 */
function get_download( $format, $post_id ) {

	if ( 0 === $post_id ) {
		return '';
	}

	switch ( $format ) {
		case 'free_sample':
			$downloadable_file = get_field( 'res_freesamp', $post_id );
			break;
		case 'download_pdf':
			$downloadable_file = get_field( 'post_full_pdf', $post_id );
			break;
		case 'download_product':
			$downloadable_file = get_field( 'free_sample_file', $post_id );
			break;
		case 'download_product_sidebar':
			$downloadable_file = get_field( 'file_available_for_download', $post_id );
			break;
		case 'download_free_report':
			$downloadable_file = get_field( 'file_to_download', $post_id );
			break;
		default:
			$downloadable_file = '';
	}
	return $downloadable_file;
}

/**
 * Disable tabindex for all forms
 */
add_filter( 'gform_tabindex', '__return_false' );


/**
 * Retrieves the value of the hidden field 'type-form'.
 *
 * @param array $form The Gravity Form array.
 *
 * @return string|null The value of the 'type-form' hidden field, or null if not found.
 */
function get_type_form( $form ) {
	$type_form_value = null;
	foreach ( $form['fields'] as $f ) {
		if ( 'hidden' === $f->type && 'type-form' === $f->name ) {
			$type_form_value = $f->defaultValue;
			break;
		}
	}
	return $type_form_value;
}


/**
 * Allow the Gravity form to stay on the page when confirmation displays.
 *
 * @param mixed $confirmation The confirmation message or redirect URL.
 * @param array $form         The Gravity Form array.
 * @param array $entry        The Gravity Form entry data.
 *
 * @return mixed The modified confirmation message or redirect URL.
 */
function show_form_after_send( $confirmation, $form, $entry ) {

	// Get the value of type-form.
	$type_form_value = null;
	foreach ( $form['fields'] as $f ) {
		if ( 'hidden' === $f->type && 'type-form' === $f->name ) {
			$field_id        = $f->id;
			$type_form_value = rgar( $entry, $field_id );
			break;
		}
	}

	$form_types = array(
		'change-password',
		'update-user',
	);
	if ( in_array( $type_form_value, $form_types, true ) ) {
		session_start(); // Start session if not already started.
		$_SESSION['gf_confirmation'] = $confirmation; // Save message.

		// Always use the current URL.
		$url_actual  = ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http' );
		$url_actual .= '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		// Clear previous parameters and add the new one.
		$url_redireccion = remove_query_arg( 'send', $url_actual );
		$url_redireccion = add_query_arg( 'send', '1', $url_redireccion );

		$confirmation = array(
			'redirect' => $url_redireccion,
		);
	}

	return $confirmation;
}
add_filter( 'gform_confirmation', __NAMESPACE__ . '\show_form_after_send', 20, 4 );
