<?php
/**
 * ACF Options for Case Studies.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\page_templates;

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', __NAMESPACE__ . '\register_video_demos_acf_fields' );

/**
 * Register ACF fields.
 */
function register_video_demos_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$public_videos = array(
		'key'           => 'field_62cc0ead7b204',
		'label'         => __('Make videos available to public (Non subscribers)', TM_LANGUAGE_DOMAIN),
		'name'          => 'public_videos',
		'type'          => 'true_false',
		'instructions'  => 'Mark this option if you want videos to be public (subscriptions settings for each video will be ignored)',
		'required'      => 0,
		'default_value' => 0,
		'ui'            => 1,
	);

	$video_title = array(
		'key'     => 'field_5fb79b58a4628',
		'label'   => __('Video title', TM_LANGUAGE_DOMAIN),
		'name'    => 'video_title',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '20',
		),
	);

	$video_embed_code = array(
		'key'     => 'field_5fb79773a4d07',
		'label'   => __('Video embed code', TM_LANGUAGE_DOMAIN),
		'name'    => 'video_embed_code',
		'type'    => 'textarea',
		'wrapper' => array(
			'width' => '20',
		),
	);

	$allowed_subscription_plan = array(
		'key'           => 'field_videos_subscription_plan',
		'label'         => __( 'Subscription Plan', TM_LANGUAGE_DOMAIN),
		'name'          => 'videos_subscription_plan',
		'type'          => 'post_object',
		'post_type'     => array( 'subscription-plan' ),
		'multiple'      => 1,
		'allow_null'    => 1,
		'return_format' => 'array',
		'ui'            => 1,
		'wrapper'       => array(
			'width' => '30',
		),
	);

	$video_description = array(
		'key'          => 'field_5fb79a68612a4',
		'label'        => __('Video description', TM_LANGUAGE_DOMAIN),
		'name'         => 'video_description',
		'type'         => 'wysiwyg',
		'tabs'         => 'all',
		'toolbar'      => 'full',
		'media_upload' => 1,
	);

	$videos_repeater = array(
		'key'          => 'field_5fb7972695b19',
		'label'        => __('Videos', TM_LANGUAGE_DOMAIN),
		'name'         => 'videos',
		'type'         => 'repeater',
		'instructions' => '',
		'layout'       => 'table',
		'button_label' => 'Add video',
		'sub_fields'   => array(
			$video_title,
			$video_embed_code,
			$allowed_subscription_plan,
			$video_description,
		),
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_5fb7970d69e67',
			'title'                 => __('Training videos New', TM_LANGUAGE_DOMAIN),
			'fields'                => array(
				$public_videos,
				$videos_repeater,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-video-demos.php',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
