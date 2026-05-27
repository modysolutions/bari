<?php

/**
 * General settings for Search functionality
 *
 * @package Tamarind_Search
 */

namespace tamarind_search;

use \WP_Post;

defined( 'ABSPATH' ) || exit;

add_filter( 'template_include', __NAMESPACE__ . '\override_search_template' );
add_filter( 'the_posts', __NAMESPACE__ . '\filter_search_results', 10, 2 );

/**
 *  Set Custom Post Type for search
 */
function search_filter( \WP_Query $query ) : \WP_Query {
	if ( $query->is_search && ! is_admin() && isset( $query->query['post_type'] ) ) {
		$pt = $query->query['post_type'];
		if ( $pt == 'product' ) {
			$query->set( 'post_type', 'product' );
		} elseif ( is_user_logged_in() ) {
			$query->set( 'post_type', array( 'post', 'regulatory_alert' ) );
		} else {
			$query->set( 'post_type', 'post' );
		}
	}

    if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
        $tax_query = $query->get( 'tax_query' ) ?: array();

        $tax_query[] = array(
            'taxonomy' => 'content_types',
            'field'    => 'slug',
            'terms'    => array( 'alerts' ),
            'operator' => 'NOT IN',
        );

        // 4. Update the query
        $query->set( 'tax_query', $tax_query );
    }

	return $query;
}


/**
 * Set order and posts per page for search results
 */
function search_order_by_date_num_posts( \WP_Query $query ) : \WP_Query {
	if ( $query->is_search && ! is_admin() ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}

	return $query;
}


/**
 * Override the search.php file of the theme with the file from the plugin.
 */
function override_search_template( string $template ) : string {
	// Check if it is a search page
	if ( is_search() ) {
		// Path to the search.php file in your plugin
		$plugin_search_template = PLUGIN_PATH . '/templates/archive.php';
		if ( file_exists( $plugin_search_template ) ) {
			return $plugin_search_template; // Load the file from the plugin
		}
	}

	return $template;
}


/**
 * Filter the search results to only include posts for non-logged in users
 */
function filter_search_results( array $posts, \WP_Query $query ) : array {
	if ( $query->is_search && ! is_admin() ) {
		// If the user is not logged in, filter the results
		if ( ! is_user_logged_in() ) {
			$filtered_posts = array();
			foreach ( $posts as $post ) {
				// Only include posts of the type 'post'
				if ( $post->post_type === 'post' ) {
					$filtered_posts[] = $post;
				}
			}

			return $filtered_posts;
		}
	}

	return $posts;
}


add_filter( 'posts_pre_query', function( $posts, $query ) {
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
        return $posts;
    }

    global $wpdb;
    $search_term = sanitize_text_field( $query->get( 's' ) );
    $table_name  = $wpdb->prefix . 'tamarind_search_index';

    $posts_per_page = (int) ( $query->get( 'posts_per_page' ) ?: get_option( 'posts_per_page' ) );
    $paged          = (int) ( $query->get( 'paged' ) ?: 1 );
    $offset         = ( $paged - 1 ) * $posts_per_page;

    // 1. Filtros exactos de los dropdowns (Geographies, Content Types, Topics)
    $where_clauses = array();
    $filter_sql    = '';

    if ( ! empty( $_GET['geographies'] ) ) {
        $geo = sanitize_text_field( wp_unslash( $_GET['geographies'] ) );
        $where_clauses[] = $wpdb->prepare( "JSON_CONTAINS(tax_geographies, %s)", '"' . $geo . '"' );
    }
    if ( ! empty( $_GET['content_types'] ) ) {
        $ct = sanitize_text_field( wp_unslash( $_GET['content_types'] ) );
        $where_clauses[] = $wpdb->prepare( "JSON_CONTAINS(tax_content_type, %s)", '"' . $ct . '"' );
    }
    if ( ! empty( $_GET['topics'] ) ) {
        $topic = sanitize_text_field( wp_unslash( $_GET['topics'] ) );
        $where_clauses[] = $wpdb->prepare( "JSON_CONTAINS(tax_topics, %s)", '"' . $topic . '"' );
    }

    if ( ! empty( $where_clauses ) ) {
        $filter_sql = ' AND ' . implode( ' AND ', $where_clauses );
    }

    // 2. Lógica de coincidencia Estricta y manejo de palabras cortas
    $word_matches = array();
    $any_tax_matches = array();

    if ( ! empty( $search_term ) ) {
        $words = array_filter( explode( ' ', $search_term ) );

        foreach ( $words as $word ) {
            $clean_word = preg_replace('/[^a-zA-Z0-9_]/', '', $word);
            if ( empty( $clean_word ) ) continue;

            if ( strlen( $clean_word ) < 3 ) {
                // FIX: MariaDB ignora < 3 chars. Usamos PCRE RegEx con delimitadores de palabra (\b)
                $regex = '\\b' . $clean_word . '\\b';

                $word_matches[] = $wpdb->prepare(
                    "(content_buffer REGEXP %s OR tax_geographies REGEXP %s OR tax_topics REGEXP %s OR tax_content_type REGEXP %s)",
                    $regex, $regex, $regex, $regex
                );
                $any_tax_matches[] = $wpdb->prepare(
                    "(content_buffer REGEXP %s OR tax_geographies REGEXP %s OR tax_topics REGEXP %s OR tax_content_type REGEXP %s)",
                    $regex, $regex, $regex, $regex
                );
            } else {
                $boolean_word = '+' . $clean_word . '*';
                $like = '%' . $wpdb->esc_like( $clean_word ) . '%';

                $word_matches[] = $wpdb->prepare(
                    "(MATCH(content_buffer) AGAINST(%s IN BOOLEAN MODE) OR tax_geographies LIKE %s OR tax_topics LIKE %s OR tax_content_type LIKE %s)",
                    $boolean_word, $like, $like, $like
                );
                $any_tax_matches[] = $wpdb->prepare(
                    "(tax_geographies LIKE %s OR tax_topics LIKE %s OR tax_content_type LIKE %s)",
                    $like, $like, $like
                );
            }
        }
    }

    $is_exact_sql = ! empty( $word_matches ) ? '(' . implode( ' AND ', $word_matches ) . ')' : '1';
    $any_tax_sql  = ! empty( $any_tax_matches ) ? '(' . implode( ' OR ', $any_tax_matches ) . ')' : '0';

    // 3. Query Principal
    $sql = $wpdb->prepare( "
        SELECT SQL_CALC_FOUND_ROWS post_id,
               ({$is_exact_sql}) as is_exact
        FROM {$table_name}
        WHERE (MATCH(content_buffer) AGAINST(%s IN NATURAL LANGUAGE MODE) > 0 OR {$any_tax_sql})
        {$filter_sql}
        ORDER BY 
            is_exact DESC,
            published_date DESC, 
            relevancy_score DESC
        LIMIT %d OFFSET %d
    ", $search_term, $posts_per_page, $offset );

    $post_ids    = $wpdb->get_col( $sql );
    $found_posts = (int) $wpdb->get_var( "SELECT FOUND_ROWS()" );

    // 4. Relleno Semántico (Si no hay suficientes resultados exactos)
    $shortfall = $posts_per_page - count( $post_ids );

    if ( $shortfall > 0 ) {
        $pad_offset  = max( 0, $offset - $found_posts );
        $exclude_sql = ! empty( $post_ids ) ? " AND post_id NOT IN (" . implode( ',', array_map( 'intval', $post_ids ) ) . ")" : "";

        $pad_sql = $wpdb->prepare( "
            SELECT post_id
            FROM {$table_name}
            WHERE 1=1 {$filter_sql} {$exclude_sql}
            ORDER BY published_date DESC, relevancy_score DESC
            LIMIT %d OFFSET %d
        ", $shortfall, $pad_offset );

        $pad_ids  = $wpdb->get_col( $pad_sql );
        $post_ids = array_merge( $post_ids, $pad_ids );
    }

    $total_available = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table_name} WHERE 1=1 {$filter_sql}" );
    $query->found_posts   = max( $found_posts, $total_available );
    $query->max_num_pages = ceil( $query->found_posts / $posts_per_page );

    if ( empty( $post_ids ) ) {
        return array();
    }

    // 5. Transformar IDs en objetos de WordPress
    $ids_string = implode( ',', array_map( 'intval', $post_ids ) );
    $raw_posts  = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE ID IN ($ids_string)" );

    $sorted_posts = array();
    foreach ( $post_ids as $id ) {
        foreach ( $raw_posts as $p ) {
            if ( $p->ID == $id ) {
                $sorted_posts[] = new WP_Post( $p );
                break;
            }
        }
    }

    return $sorted_posts;

}, 10, 2 );
