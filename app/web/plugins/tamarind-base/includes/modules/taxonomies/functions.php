<?php
/**
 * Functions for Taxonomies
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\taxonomies;

defined( 'ABSPATH' ) || exit;

use function tamarind_subscriptions\subscription_plan\{get_data_plan};

const PLUGIN_PATH = __DIR__;

// Load all taxonomies.
require_once PLUGIN_PATH . '/includes/topics.php';
require_once PLUGIN_PATH . '/includes/geography.php';
require_once PLUGIN_PATH . '/includes/content-types.php';


add_action( 'init', __NAMESPACE__ . '\add_category_tags_to_cpt' );
add_filter( 'taxonomy_template', __NAMESPACE__ . '\tamarind_custom_taxonomy_templates' );
add_filter( 'theme_page_templates', __NAMESPACE__ . '\tamarind_register_page_templates' );
add_filter( 'template_include', __NAMESPACE__ . '\tamarind_load_page_template' );
add_filter( 'template_include', __NAMESPACE__ . '\content_types_geo_hierarchy', 99 );
add_action( 'pre_get_posts', __NAMESPACE__ . '\modify_content_types_taxonomy_query' );


/**
 * Add built-in taxonomies to custom post types.
 *
 * @return void
 */
function add_category_tags_to_cpt(): void {
	register_taxonomy_for_object_type( 'post_tag', 'regulatory_alert' );
}


/**
 * Loads the Taxonomy archive template
 *
 * @param string $template Current template.
 * @return string Taxonomy archive template.
 */
function tamarind_custom_taxonomy_templates( $template ): string {

	if ( ! is_tax() ) {
		return $template;
	}

	$queried_object = get_queried_object();
	if ( ! isset( $queried_object->taxonomy ) ) {
		return $template;
	}
	$current_taxonomy = $queried_object->taxonomy;

	$taxonomy_templates = array(
		'topics'        => 'taxonomy-topics.php',
		'geography'     => 'taxonomy-geography.php',
		'content_types' => 'taxonomy-content_types.php',
	);

	if ( $current_taxonomy && array_key_exists( $current_taxonomy, $taxonomy_templates ) ) {
		$template_file = $taxonomy_templates[ $current_taxonomy ];
		$template_path = plugin_dir_path( __FILE__ ) . 'templates/' . $template_file;

		if ( file_exists( $template_path ) ) {
			return $template_path;
		}
	}

	return $template;
}


/**
 * Register custom page templates.
 *
 * @param array $templates Existing page templates.
 *
 * @return array Modified page templates.
 */
function tamarind_register_page_templates( array $templates ): array {
	$plugin_templates = array(
		'page-territory.php' => __( 'Page Territory', TM_LANGUAGE_DOMAIN ),
	);

	return array_merge( $templates, $plugin_templates );
}

/**
 * Load custom page templates from the plugin.
 *
 * @param string $template Current template.
 *
 * @return string Modified template path if custom template is used, otherwise original template.
 */
function tamarind_load_page_template( string $template ): string {
	global $post;

	if ( ! $post || ! is_page() ) {
		return $template;
	}

	$page_template = get_page_template_slug( $post->ID );

	if ( 'page-territory.php' === $page_template ) {
		$plugin_template_path = plugin_dir_path( __FILE__ ) . 'templates/page-territory.php';

		if ( file_exists( $plugin_template_path ) ) {
			return $plugin_template_path;
		}
	}

	return $template;
}


/**
 * Loads a custom taxonomy template for content types based on the 'geography' query parameter.
 *
 * @param string $template The current template path.
 *
 * @return string The modified template path if conditions are met, otherwise the original template.
 */
function content_types_geo_hierarchy( string $template ): string {
	if ( isset( $_GET['geography'] ) ) {
		$geo_param = sanitize_text_field( $_GET['geography'] );

		if ( is_tax( 'geography' ) || 'americas' === $geo_param ) {
			$plugin_template = PLUGIN_PATH . '/templates/taxonomy-content_types.php';

			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}

			$theme_template = locate_template( array( 'taxonomy-content_types.php' ) );
			if ( '' !== $theme_template ) {
				return $theme_template;
			}
		}
	}
	return $template;
}


/**
 * Modify the main query on 'content_types' taxonomy archive pages.
 *
 * If the 'geography' query variable is set to 'americas', adjust the query to include
 * posts from specific geography terms and set posts per page to 50.
 *
 * @param \WP_Query $query The WP_Query instance (passed by reference).
 *
 * @return void
 */
function modify_content_types_taxonomy_query( \WP_Query $query ): void {
	if ( ! is_admin() && $query->is_main_query() && is_tax( 'content_types' ) ) {
		$geo_slug = get_query_var( 'geography' );

		if ( 'americas' === $geo_slug ) {
			$query->set( 'posts_per_page', 50 );
			
			$tax_query = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'content_types',
					'field'    => 'slug',
					'terms'    => $query->get_queried_object()->slug,
				),
				array(
					'taxonomy' => 'geography',
					'field'    => 'slug',
					'terms'    => array( 'north-america', 'south-america', 'central-america' ),
				),
			);

			$query->set( 'tax_query', $tax_query );
		}
	}
}


/**
 * Displays featured posts for a given taxonomy term on taxonomy pages.
 *
 * @param array|string $term_slug Slug or array of slugs of the taxonomy term(s).
 * @param int|string   $position Position identifier for the module.
 * @param int          $num_posts Number of posts to display.
 * @param string       $geo Geography slug.
 * @param string       $topic Topic slug.
 * @param int          $days Number of days to consider content as new.
 *
 * @return string
 */
function home_module( array|string $term_slug, int|string $position, int $num_posts, string $geo, string $topic = '', int $days = 30 ) : string {
	if ( is_array( $term_slug ) ) {
		$single_term_slug = $term_slug[0];
	} else {
		$single_term_slug = $term_slug;
	}

	$term = get_term_by( 'slug', $single_term_slug, 'content_types' );
	if ( ! $term ) {
		return '';
	}

	if ($single_term_slug == 'alerts') {
		$http_params = array();
		if ($geo != '') {
			$http_params = array(
				'geoalerts' => $geo,
			);
		}
		$term_link = add_query_arg($http_params, get_post_type_archive_link('regulatory_alert'));
		$geo_group = alerts_europe_us_news();
		$html_content = apply_filters( 'tm_get_show_alerts', $geo, $geo_group, $topic );
	} else {
        $http_params = array();
        if(!empty($geo)) {
	        $http_params['geography'] = $geo;
        }
		if(!empty($topic)) {
			$http_params['tm-topics'] = $topic;
		}
		$term_link = add_query_arg($http_params, get_term_link($term));
		if ( is_wp_error( $term_link ) ) {
			$term_link = '#';
		}
		$html_content = show_featured_posts(
			$term_slug,
			$num_posts,
			false,
			$geo,
			$topic,
			$days
		);
	}

	if ( ! empty( $html_content ) ) {
		ob_start();
		?>

		<div id="recents-group-<?php echo esc_attr( $position ); ?>" class="recents-group">

			<?php
			if ( 'alerts' === $single_term_slug ) {
				do_action( 'tm_add_alerts_tooltip' );
			}
			?>

			<a href="<?php echo esc_url( $term_link ); ?>" class="new-module-label label-secondary">
				<?php echo esc_html( $term->name ); ?>
			</a>

			<?php
			if ( ! empty( $single_term_slug ) ) :
				$term_obj               = get_term_by( 'slug', $single_term_slug, 'content_types' );
				$term_short_description = get_field( 'short_description', $term_obj );
				if ( ! empty( $term_short_description ) ) :
					?>
					<div class="term-short-description"><div>
							<?php echo esc_html( $term_short_description ); ?>
						</div>
					</div>
					<?php
				endif;
			endif;
			?>

			<div class="recents-content">

				<?php echo $html_content; // phpcs:ignore ?>

			</div>

			<a class="tm-btn btn-transparent" href="<?php echo esc_url( $term_link ); ?>">
				<?php echo esc_html( sprintf( 'More %s', $term->name ) ); ?>
			</a>

		</div>

		<?php
		return ob_get_clean();
	}

	return '';
}


/**
 * Prints the modules on taxonomy pages.
 *
 * @param array  $terms Array of terms with their settings.
 * @param string $geo Geography slug.
 * @param string $topic Topic slug.
 *
 * @return string
 */
function print_modules( array $terms, string $geo, string $topic = '' ): string {
	ob_start();
	?>
	<section id="modules-home">
		<div class="tm-layout-grid tm-layout-grid--xl">

			<?php
			$modules_counter = 1;
			foreach ( $terms as $term ) {
				$module_html = home_module(
					$term['slug'],
					$modules_counter,
					$term['posts'],
					$geo,
					$topic,
					$term['days']
				);
				if ( ! empty( $module_html ) ) {
					if ( ( 1 === $modules_counter % 2 ) && ( 5 === $modules_counter ) && ( ! empty( $geo ) ) ) {
						if ( get_field( 'csb_show', 'options' ) && ! is_user_logged_in() ) {
							echo '</div>';
							include plugin_dir_path( __FILE__ ) . 'template-parts/banner-cta-country-subscription-banner.php';
							echo '<div class="tm-layout-grid tm-layout-grid--xl">';
						}
					}

					$modules_counter ++;
				}
				echo $module_html;
			}
			?>

	</div></section>
	<?php

	return ob_get_clean();
}


/**
 * Get IDs of 'geography' terms considered as 'international'.
 *
 * This function retrieves all 'geography' terms and excludes those that are part of
 * Europe (including UK) and the United States, returning the remaining term IDs.
 *
 * @return array Array of term IDs considered as 'international'.
 */
function get_ids_international() : array {
	$ids_all = array();
	$geo_terms = get_terms( 'geography', array( 'hide_empty' => 0 ) );

	foreach ( $geo_terms as $geo_term ) {
		$ids_all[] = $geo_term->term_id;
	}

	$ids_not_in = array();

	$geo_terms = get_terms(
		'geography',
		array(
			'hide_empty' => 0,
			'slug'       => 'europe',
		)
	);
	foreach ( $geo_terms as $geo_term ) {
		$ids_not_in[] = $geo_term->term_id;
		$id_europe = $geo_term->term_id;
	}

	$geo_terms = get_terms(
		'geography',
		array(
			'hide_empty' => 0,
			'parent'     => $id_europe,
		)
	);
	foreach ( $geo_terms as $geo_term ) {
		$ids_not_in[] = $geo_term->term_id;
		if ( 'united-kingdom' === $geo_term->slug ) {
			$id_uk = $geo_term->term_id;
		}
	}

	$geo_terms = get_terms(
		'geography',
		array(
			'hide_empty' => 0,
			'parent'     => $id_uk,
		)
	);
	foreach ( $geo_terms as $geo_term ) {
		$ids_not_in[] = $geo_term->term_id;
	}

	$geo_terms = get_terms(
		'geography',
		array(
			'hide_empty' => 0,
			'slug'       => 'north-america',
		)
	);
	foreach ( $geo_terms as $geo_term ) {
		$ids_not_in[] = $geo_term->term_id;
	}

	$geo_terms = get_terms(
		'geography',
		array(
			'hide_empty' => 0,
			'slug'       => 'united-states',
		)
	);
	foreach ( $geo_terms as $geo_term ) {
		$ids_not_in[] = $geo_term->term_id;
		$id_usa = $geo_term->term_id;
	}

	$geo_terms = get_terms(
		'geography',
		array(
			'hide_empty' => 0,
			'parent'     => $id_usa,
		)
	);
	foreach ( $geo_terms as $geoterm ) {
		$ids_not_in[] = $geoterm->term_id;
	}

	return array_diff( $ids_all, $ids_not_in );
}


/**
 * Displays featured posts based on given taxonomy terms and filters.
 *
 * @param array|string $terms_input Slug or array of slugs of the taxonomy term(s).
 * @param int          $num_posts Number of posts to display.
 * @param string       $taxonomy Taxonomy name (default is 'content_types').
 * @param string       $geo Geography slug.
 * @param string       $topic Topic slug.
 * @param int          $days Number of days to consider content as new.
 *
 * @return string HTML output of the featured posts.
 */
function show_featured_posts( string|array $terms_input, int $num_posts, string $taxonomy, string $geo, string $topic = '', int $days = 30 ) : string {
	if ( empty( $taxonomy ) ) {
		$taxonomy = 'content_types';
	}

	$cache_key_args = array(
		'subscription_plan' => \tamarind_subscriptions\subscription_plan\get_user_plan_id(),
		'terms_input' => $terms_input,
		'num_posts'   => $num_posts,
		'taxonomy'    => $taxonomy,
		'geo'         => $geo,
		'topic'       => $topic,
		'days'        => $days,
	);

	ksort( $cache_key_args );
	$cache_key = 'featured_posts_result_' . md5( serialize( $cache_key_args ) );
	$cache_group = 'featured_posts';
	$featured_query = wp_cache_get( $cache_key, $cache_group );
	if ( ! $featured_query ) {
		$query_args = array(
			'posts_per_page' => $num_posts,
			'post_status'    => 'publish',
			'tax_query'      => array( // phpcs:ignore
				'relation' => 'AND',
				array(
					'taxonomy'         => $taxonomy,
					'field'            => 'slug',
					'terms'            => $terms_input,
					'operator'         => 'IN',
					'include_children' => true,
				),
			),
		);

		if ( ! empty( $geo ) ) {
			if ( 'international' === $geo ) {
				$ids_international = get_ids_international();
				$query_args['tax_query'][] = array(
					'taxonomy'         => 'geography',
					'field'            => 'term_id',
					'include_children' => false,
					'terms'            => $ids_international,
					'operator'         => 'IN',
				);
			} elseif ( 'americas' === $geo ) {
				$query_args['tax_query'][] = array(
					'taxonomy' => 'geography',
					'field'    => 'slug',
					'terms'    => array( 'north-america', 'south-america', 'central-america' ),
					'operator' => 'IN',
				);
			} else {
				$query_args['tax_query'][] = array(
					'taxonomy' => 'geography',
					'field'    => 'slug',
					'terms'    => $geo,
				);
			}
		}

		if ( ! empty( $topic ) ) {
			$query_args['tax_query'][] = array(
				'taxonomy' => 'topics',
				'field'    => 'slug',
				'terms'    => $topic,
			);
		}

		$featured_query = new \WP_Query( $query_args );
        wp_cache_set( $cache_key, $featured_query, $cache_group, 10 * MINUTE_IN_SECONDS );
	}

	$output_html    = '';
	$counter        = 0;

	if ( $featured_query->have_posts() ) {
		ob_start();

		while ( $featured_query->have_posts() ) :
			$featured_query->the_post();
			$counter++;

			if ( 1 === $counter ) {
				echo '<div class="tm-layout-grid tm-layout-grid--fullwidth recent_first">';
				\tamarind_base\print_post_card( get_post(), '', 'date-restricted', '', $days );
				echo '</div>';
			} else {
				if ( 0 === $counter % 2 ) {
					echo '<div class="tm-layout-grid recent_second">';
				}

				\tamarind_base\print_post_card( get_post(), '', 'date-restricted', '', $days );

				if ( 1 === $counter % 2 ) {
					echo '</div>';
				}
			}
		endwhile;

		if ( $counter > 1 && 0 === $counter % 2 ) {
			echo '</div>';
		}

		$output_html = ob_get_clean();
	}

	wp_reset_postdata();
	return $output_html;
}


/**
 * Get geography terms for alerts in Europe and US based on user group.
 * This function checks the user's group and retrieves the relevant geography terms
 * associated with alerts for Europe and the United States.
 *
 * @return string
 */
function alerts_europe_us_news() : string {
	$user_id_in = apply_filters( 'determine_current_user', false );
	wp_set_current_user( $user_id_in );

	$dataPlan = get_data_plan();
	$termsGeoTax = $dataPlan['plan_geo_alerts_tax'] ?? array();

	$geoGroup = '';
	if ( empty( $termsGeoTax ) ) {
		return $geoGroup; 
	}
	foreach ( $termsGeoTax as $geoValue ) {
		$country = get_term_by( 'id', $geoValue, 'geography' );
		if ( ( $country->slug == 'united-states' ) || ( $country->slug == 'europe' ) ) {
			$geoGroup = $country->slug;
		}
	}
	return $geoGroup;
}

/**
 * Retrieve all content type terms.
 *
 * @return array|string|\WP_Error Array of WP_Term objects representing all content types.
 */
function get_all_content_types(): array|string|\WP_Error {
	$terms_args        = array( 'hide_empty' => false );
	return get_terms( 'content_types', $terms_args );
}
