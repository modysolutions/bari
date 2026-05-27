<?php
/**
 * ACF functions for Tamarind PDFs.
 *
 * @package Tamarind_Pdfs
 */

namespace tamarind_pdfs;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the ACF fields.
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	add_filter( 'acf/init', __NAMESPACE__ . '\register_acf_fields' );

	add_action( 'acf/save_post', __NAMESPACE__ . '\save_options_generate_pdfs', 20 );
	add_action( 'admin_notices', __NAMESPACE__ . '\display_notices' );
}

/**
 * Registers the ACF fields.
 */
function register_acf_fields() {
	acf_add_options_sub_page(
		array(
			'page_title'  => 'Generate PDF Settings',
			'menu_title'  => 'Generate PDF',
			'parent_slug' => 'edit.php',
			'menu_slug'   => 'generate-pdf-settings',
			'capability'  => 'manage_options',
		)
	);

	$manual_generate_pdf = array(
		'key'           => 'field_manual_generate_pdf',
		'label'         => 'Generate PDFs manually',
		'name'          => 'manual_generate_pdf',
		'type'          => 'true_false',
		'instructions'  => 'Enable this option to manually generate pending PDFs and Update. This will run the PDF generation immediately if it is not already running.',
		'default_value' => 0,
		'ui'            => 1,
	);

	$information = array(
		'key'     => 'field_manual_generate_pdf_message',
		'label'   => 'Information',
		'name'    => 'information',
		'type'    => 'message',
		'message' => 'This option allows you to generate pending PDFs immediately instead of waiting for the scheduled process (every 15 minutes). If the generation process is already running, manual execution will be blocked.',
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_generate_pdf',
			'title'    => 'Generate PDF Settings',
			'fields'   => array(
				$manual_generate_pdf,
				$information,
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'generate-pdf-settings',
					),
				),
			),
		)
	);

	acf_add_options_sub_page(
		array(
			'page_title'  => 'PDF Settings',
			'menu_title'  => 'PDF Settings',
			'menu_slug'   => 'tamarind-pdf-options',
			'capability'  => 'manage_options',
			'parent_slug' => 'tamarind-base',
			'redirect'    => false,
		)
	);

	$tab_pdf_content = array(
		'key'       => 'field_pdf_content',
		'label'     => 'Content',
		'name'      => 'pdf_content',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$footer_img = array(
		'key'           => 'field_5fb3d6739fd3d',
		'label'         => 'Common FOOTER',
		'name'          => 'common_footer',
		'type'          => 'image',
		'instructions'  => 'Horizontal image with footer copyright text inside image',
		'wrapper'       => array(
			'width' => '60',
			'class' => '',
			'id'    => '',
		),
		'return_format' => 'url',
		'preview_size'  => 'full',
	);

	$footer_text_left_logo = array(
		'key'     => 'footer_text_left_logo',
		'label'   => 'Text in logo footer',
		'name'    => 'footer_text_left_logo',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '40',
			'class' => '',
			'id'    => '',
		),
	);

	$footer_text = array(
		'key'          => 'field_629f36cc4c6c9',
		'label'        => 'Text for PDFs footer',
		'name'         => 'common_footer_downloads',
		'type'         => 'wysiwyg',
		'wrapper'      => array(
			'width' => '50',
			'class' => '',
			'id'    => '',
		),
		'toolbar'      => 'full',
		'media_upload' => 1,
	);

	$footer_links = array(
		'key'               => 'field_629f39f2a43d1',
		'label'             => 'Links for PDFs footer',
		'name'              => 'common_footer_links_downloads',
		'aria-label'        => '',
		'type'              => 'wysiwyg',
		'instructions'      => '',
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '50',
			'class' => '',
			'id'    => '',
		),
		'default_value'     => '',
		'allow_in_bindings' => 1,
		'tabs'              => 'all',
		'toolbar'           => 'full',
		'media_upload'      => 1,
		'delay'             => 0,
	);

	$tab_pdf_dessign = array(
		'key'       => 'field_pdf_design',
		'label'     => 'Design',
		'name'      => 'pdf_design',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$home_img = array(
		'key'           => 'pdf_home_image',
		'label'         => 'Home Image',
		'name'          => 'home_image',
		'type'          => 'image',
		'instructions'  => 'Home page image for PDFs',
		'wrapper'       => array(
			'width' => '33',
			'class' => '',
			'id'    => '',
		),
		'return_format' => 'url',
		'preview_size'  => 'full',
	);

	$header_img = array(
		'key'           => 'pdf_header_image',
		'label'         => 'Header Image',
		'name'          => 'header_image',
		'type'          => 'image',
		'instructions'  => 'Header Image for PDFs',
		'wrapper'       => array(
			'width' => '33',
			'class' => '',
			'id'    => '',
		),
		'return_format' => 'url',
		'preview_size'  => 'full',
	);

	$header_logo = array(
		'key'           => 'pdf_header_logo',
		'label'         => 'Header Logo',
		'name'          => 'header_logo',
		'type'          => 'image',
		'instructions'  => 'Header Logo for PDFs',
		'wrapper'       => array(
			'width' => '33',
			'class' => '',
			'id'    => '',
		),
		'return_format' => 'url',
		'preview_size'  => 'full',
	);

	$color_primary = array(
		'key'     => 'field_pdf_primary_color',
		'label'   => 'Primary Color',
		'name'    => 'primary_color',
		'type'    => 'color_picker',
		'wrapper' => array(
			'width' => '20',
		),
	);

	$color_date = array(
		'key'     => 'field_pdf_date_color',
		'label'   => 'Date Color',
		'name'    => 'date_color',
		'type'    => 'color_picker',
		'wrapper' => array(
			'width' => '20',
		),
	);

	$color_link = array(
		'key'     => 'field_pdf_link_color',
		'label'   => 'Link Color',
		'name'    => 'link_color',
		'type'    => 'color_picker',
		'wrapper' => array(
			'width' => '20',
		),
	);

	$color_title_h3 = array(
		'key'     => 'field_pdf_title_h3_color',
		'label'   => 'Title H3 Color',
		'name'    => 'title_h3_color',
		'type'    => 'color_picker',
		'wrapper' => array(
			'width' => '20',
		),
	);

	$color_title_h4 = array(
		'key'     => 'field_pdf_title_h4_color',
		'label'   => 'Title H4 Color',
		'name'    => 'title_h4_color',
		'type'    => 'color_picker',
		'wrapper' => array(
			'width' => '20',
		),
	);

	$tab_pdf_settings = array(
		'key'       => 'field_generate_settings',
		'label'     => 'Generate Settings',
		'name'      => 'pdf_generate_settings',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$generate_pdfs = array(
		'key'          => 'field_65770e32a905c',
		'label'        => 'Generate PDF by CRON',
		'name'         => 'cron_pdfs',
		'type'         => 'true_false',
		'instructions' => 'Generate PDFs through CRON',
		'wrapper'      => array(
			'width' => '33',
			'class' => '',
			'id'    => '',
		),
		'ui_on_text'   => '',
		'ui_off_text'  => '',
		'ui'           => 1,
	);

	$reset_pdfs = array(
		'key'          => 'field_65770d6aa905b',
		'label'        => 'Reset PDFs',
		'name'         => 'reset_pdfs',
		'type'         => 'true_false',
		'instructions' => 'Reset PDF control',
		'wrapper'      => array(
			'width' => '33',
			'class' => '',
			'id'    => '',
		),
		'ui_on_text'   => '',
		'ui_off_text'  => '',
		'ui'           => 1,
	);

	$pdf_downloads = array(
		'key'                  => 'field_64ba7bbf91c25',
		'label'                => 'Trial PDF Downloads',
		'name'                 => 'trial_pdf_downloads',
		'type'                 => 'taxonomy',
		'instructions'         => 'Taxonomy that allows visitors to download the entire content in PDF',
		'wrapper'              => array(
			'width' => '33',
			'class' => '',
			'id'    => '',
		),
		'taxonomy'             => 'content_types',
		'add_term'             => 1,
		'save_terms'           => 0,
		'load_terms'           => 0,
		'return_format'        => 'id',
		'field_type'           => 'multi_select',
		'allow_null'           => 0,
		'bidirectional'        => 0,
		'multiple'             => 0,
		'bidirectional_target' => array(),
	);

	$pdf_path_files = array(
		'key'          => 'field_pdf_path_files',
		'label'        => 'PDF Path Files',
		'name'         => 'pdf_path_files',
		'type'         => 'text',
		'instructions' => 'Path to save PDF files (into uploads folder)',
		'wrapper'      => array(
			'width' => '33',
			'class' => '',
			'id'    => '',
		),
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_pdf_settings',
			'title'    => 'PDF Settings',
			'fields'   => array(
				$tab_pdf_content,
				$footer_img,
				$footer_text_left_logo,
				$footer_text,
				$footer_links,
				$tab_pdf_dessign,
				$color_primary,
				$color_date,
				$color_link,
				$color_title_h3,
				$color_title_h4,
				$home_img,
				$header_img,
				$header_logo,
				$tab_pdf_settings,
				$generate_pdfs,
				$reset_pdfs,
				$pdf_downloads,
				$pdf_path_files,
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tamarind-pdf-options',
					),
				),
			),
		)
	);
}


/**
 * Save the options to generate PDFs manually.
 *
 * @param int $post_id Post ID.
 */
function save_options_generate_pdfs( $post_id ) {
	if ( 'options' !== $post_id ) {
		return;
	}

	if ( strpos( get_current_screen()->id, 'generate-pdf-settings' ) !== false ) {
		update_option( 'is_generating_pdfs', false );

		// Check if there is already a running process.
		if ( get_option( 'is_generating_pdfs' ) ) {
			set_transient( 'pdf_notice', '⚠️ PDF generation is already running. Manual execution was blocked.', 10 );
			return;
		}

		// Check if the user has enabled manual generation.
		if ( get_field( 'manual_generate_pdf', 'options' ) ) {
			$existing_ids = get_option( 'generate_post_pdf', '' );

			if ( empty( $existing_ids ) ) {
				set_transient( 'pdf_notice', '❌ No pending PDFs to generate.', 10 );
				return;
			}

			$ids = explode( ',', $existing_ids );
			$generated_titles = array();

			foreach ( $ids as $post_id ) {
				if ( get_post_status( $post_id ) ) { // Verify that the post still exists.
					$post = get_post( $post_id );
					set_pdfs_to_post( $post_id );
					$generated_titles[] = esc_html( $post->post_title );
					$existing_ids = remove_id_generate_post_pdf( $post_id, $existing_ids );
					if ( empty( $existing_ids ) ) {
						break;
					}
				} else {
					$existing_ids = remove_id_generate_post_pdf( $post_id, $existing_ids );
				}
			}

			// Reset the ACF field to false.
			update_field( 'manual_generate_pdf', false, 'options' );

			// Message with the generated titles.
			if ( ! empty( $generated_titles ) ) {
				$message = '✅ PDFs generated successfully for: <br><strong>' . implode( '<br>', $generated_titles ) . '</strong>';
			} else {
				$message = '❌ No valid posts found to generate PDFs.';
			}

			set_transient( 'pdf_notice', $message, 10 );
		} else {
			set_transient( 'pdf_notice', '❌ Manual generation is disabled temporaly because process executing now.', 10 );
		}
	}
}

/**
 * Display the notice in the admin panel.
 */
function display_notices() {
	$message = get_transient( 'pdf_notice' );
	if ( $message ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		delete_transient( 'pdf_notice' );

		// Reset the ACF field to false.
		update_field( 'manual_generate_pdf', false, 'options' );
	}
}
