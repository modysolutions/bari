<?php
/**
 * ACF Group: Post Ratings
 * Stores user ratings for each post
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\ratings;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

// POST RATINGS FIELD (for posts).

$post_rating_date = array(
	'key'               => 'field_rating_post_date',
	'label'             => __('Date', TM_LANGUAGE_DOMAIN),
	'name'              => 'date',
	'type'              => 'date_time_picker',
	'instructions'      => __('Date and time when the rating was submitted', TM_LANGUAGE_DOMAIN),
	'wrapper'           => array(
		'width' => '20',
	),
	'display_format'    => 'd/m/Y g:i a',
	'return_format'     => 'Y-m-d H:i:s',
	'first_day'         => 1,
);

$post_rating_username = array(
	'key'               => 'field_rating_post_username',
	'label'             => __('Username', TM_LANGUAGE_DOMAIN),
	'name'              => 'username',
	'type'              => 'text',
	'instructions'      => __('Name of the user who submitted the rating', TM_LANGUAGE_DOMAIN),
	'wrapper'           => array(
		'width' => '25',
	),
);

$post_rating_email = array(
	'key'               => 'field_rating_post_email',
	'label'             => __('Email', TM_LANGUAGE_DOMAIN),
	'name'              => 'email',
	'type'              => 'email',
	'instructions'      => __('Email address of the user', TM_LANGUAGE_DOMAIN),
	'wrapper'           => array(
		'width' => '25',
	),
);

$post_rating_value = array(
	'key'               => 'field_rating_post_value',
	'label'             => __('Rating', TM_LANGUAGE_DOMAIN),
	'name'              => 'rating',
	'type'              => 'number',
	'instructions'      => __('Star rating value from 1 to 5', TM_LANGUAGE_DOMAIN),
	'wrapper'           => array(
		'width' => '10',
	),
	'min'               => 1,
	'max'               => 5,
	'step'              => 1,
);

$post_rating_user_id = array(
	'key'               => 'field_rating_post_user_id',
	'label'             => __('User ID', TM_LANGUAGE_DOMAIN),
	'name'              => 'user_id',
	'type'              => 'number',
	'instructions'      => __('WordPress User ID for reference', TM_LANGUAGE_DOMAIN),
	'wrapper'           => array(
		'width' => '20',
	),
	'min'               => 1,
	'step'              => 1,
);

$post_ratings_repeater = array(
	'key'               => 'field_rating_post_ratings',
	'label'             => __('Post Ratings', TM_LANGUAGE_DOMAIN),
	'name'              => 'post_ratings',
	'type'              => 'repeater',
	'instructions'      => __('All ratings submitted for this post', TM_LANGUAGE_DOMAIN),
	'collapsed'         => 'field_rating_post_date',
	'layout'            => 'table',
	'button_label'      => __('Add New Rating', TM_LANGUAGE_DOMAIN),
	'sub_fields'        => array(
		$post_rating_date,
		$post_rating_username,
		$post_rating_email,
		$post_rating_value,
		$post_rating_user_id,
	),
);

acf_add_local_field_group(
	array(
		'key'                   => 'group_rating_post_ratings',
		'title'                 => __('Post Ratings', TM_LANGUAGE_DOMAIN),
		'fields'                => array( $post_ratings_repeater ),
		'location'              => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'post',
				),
			),
		),
		'menu_order'            => 100,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
		'description'           => __('Stores all user ratings for this post', TM_LANGUAGE_DOMAIN),
	)
);

// USER RATINGS FIELD (for user profiles).
$user_rating_date = array(
	'key'               => 'field_rating_user_date',
	'label'             => __('Date', TM_LANGUAGE_DOMAIN),
	'name'              => 'date',
	'type'              => 'date_time_picker',
	'instructions'      => __('Date and time when the rating was submitted', TM_LANGUAGE_DOMAIN),
	'wrapper'           => array(
		'width' => '20',
	),
	'display_format'    => 'd/m/Y g:i a',
	'return_format'     => 'Y-m-d H:i:s',
	'first_day'         => 1,
);

$user_rating_post_id = array(
	'key'               => 'field_rating_user_post_id',
	'label'             => __('Post ID', TM_LANGUAGE_DOMAIN),
	'name'              => 'post_id',
	'type'              => 'number',
	'instructions'      => __('ID of the rated post', TM_LANGUAGE_DOMAIN),
	'wrapper'           => array(
		'width' => '15',
	),
	'min'               => 1,
	'step'              => 1,
);

$user_rating_post_title = array(
	'key'               => 'field_rating_user_post_title',
	'label'             => __('Post Title', TM_LANGUAGE_DOMAIN),
	'name'              => 'post_title',
	'type'              => 'text',
	'instructions'      => __('Title of the rated post', TM_LANGUAGE_DOMAIN),
	'wrapper'           => array(
		'width' => '30',
	),
);

$user_rating_value = array(
	'key'               => 'field_rating_user_value',
	'label'             => __('Rating', TM_LANGUAGE_DOMAIN),
	'name'              => 'rating',
	'type'              => 'number',
	'instructions'      => __('Star rating value from 1 to 5', TM_LANGUAGE_DOMAIN),
	'wrapper'           => array(
		'width' => '10',
	),
	'min'               => 1,
	'max'               => 5,
	'step'              => 1,
);

$user_rating_post_url = array(
	'key'               => 'field_rating_user_post_url',
	'label'             => __('Post URL', TM_LANGUAGE_DOMAIN),
	'name'              => 'post_url',
	'type'              => 'url',
	'instructions'      => __('URL of the rated post', TM_LANGUAGE_DOMAIN),
	'wrapper'           => array(
		'width' => '25',
	),
);

$user_ratings_repeater = array(
	'key'               => 'field_rating_user_ratings',
	'label'             => __('User Ratings', TM_LANGUAGE_DOMAIN),
	'name'              => 'user_ratings',
	'type'              => 'repeater',
	'instructions'      => __('All ratings submitted by this user', TM_LANGUAGE_DOMAIN),
	'collapsed'         => 'field_rating_user_date',
	'layout'            => 'table',
	'button_label'      => __('Add New Rating', TM_LANGUAGE_DOMAIN),
	'sub_fields'        => array(
		$user_rating_date,
		$user_rating_post_id,
		$user_rating_post_title,
		$user_rating_value,
		$user_rating_post_url,
	),
);

acf_add_local_field_group(
	array(
		'key'                   => 'group_rating_user_ratings',
		'title'                 => __('User Ratings History', TM_LANGUAGE_DOMAIN),
		'fields'                => array( $user_ratings_repeater ),
		'location'              => array(
			array(
				array(
					'param'    => 'user_form',
					'operator' => '==',
					'value'    => 'all',
				),
			),
		),
		'menu_order'            => 100,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
		'description'           => __('Stores all ratings submitted by this user', TM_LANGUAGE_DOMAIN),
	)
);
