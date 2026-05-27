<?php
/**
 * Cli functions for Tamarind PDFs.
 *
 * @package Tamarind_Pdfs
 */

namespace tamarind_pdfs;

defined( 'ABSPATH' ) || exit;

function reset_pdfs () {

	$cron_pdfs  = get_field( 'cron_pdfs', 'option' );
	$reset_pdfs = get_field( 'reset_pdfs', 'option' );

	if ( $cron_pdfs ) {
		if ( $reset_pdfs ) {
			$posts = get_posts( array(
				'post_type' => array('post'),
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => 'post_create_pdf',
						'compare' => 'EXISTS'
					)
					
				)
			) );

			echo "Posts with 'post_create_pdf': " . count( $posts ) . "\n";
			
			$contador = 0;
			foreach ( $posts as $post ) {
				update_post_meta( $post->ID, 'post_create_pdf', '0' );
				$contador++;
			}
		
			echo "Posts updated: " . $contador . "\n";
			// update reset_pdfs to false.
			update_field( 'reset_pdfs', false, 'option' );
		} else {
			echo "Reset PDFs is not active\n";
		}
	} else {
		echo "Cron PDFs is not active\n";
	}
}

function set_all_pdfs() {

	$cron_pdfs  = get_field( 'cron_pdfs', 'option' );
	$reset_pdfs = get_field( 'reset_pdfs', 'option' );

	// si no se han borrado flag en pdfs no se genera nada.
	if ( $cron_pdfs && !$reset_pdfs  ) {
		$posts = get_posts( array(
			'post_type' => array('post'),
			'posts_per_page' => 25,
			'meta_query' => array(
				array(
					'key' => 'post_create_pdf',
					'value' => '0',
					'compare' => '='
				)
			)
		) );

		echo "Posts to update: " . count( $posts ) . "\n";

		if ( count( $posts ) == 0 ) {
			// si no hay posts que actualizar, se desactiva el cron
			update_field( 'cron_pdfs', false, 'option' );
		} else {
			$contador = 0;
			foreach ( $posts as $post ) {
				echo "GenPDF/PostID: " . $post->ID . "\n";
                set_pdfs_to_post( $post->ID );
				echo " OK!\n";
				update_post_meta( $post->ID, 'post_create_pdf', '1' );
				$contador++;
			}

			echo "Posts updated: " . $contador . "\n";
		}
	} else {
		if ( $reset_pdfs ) {
			echo "Reset PDFs is active\n";
		} else {
			echo "Cron PDFs is not active\n";
		}
	}
}

function remove_id_generate_post_pdf( $post_id, $existing_ids ) {
	if ( empty( $existing_ids ) ) {
		return '';
	}

	$ids_array = explode( ',', rtrim( $existing_ids, ',' ) );
	$ids_array = array_filter( $ids_array, function( $id ) use ( $post_id ) {
		return (string) $id !== (string) $post_id;
	} );
	$updated_ids = implode( ',', $ids_array );
	update_option( 'generate_post_pdf', $updated_ids );
	return $updated_ids;
}

function generate_post_pdfs() {

    $existing_ids = get_option( 'generate_post_pdf', '' );
    
    if ( empty( $existing_ids ) ) {
        echo "No pending PDFs to generate." . "\n";
        return;
    }

	// Set option to true while processing.
    update_option( 'is_generating_pdfs', true );
    
    $ids = explode( ',', $existing_ids );
    
    foreach ( $ids as $post_id ) {
        if ( get_post_status( $post_id ) ) { // Verify that the post still exists.
            set_pdfs_to_post( $post_id );

			$existing_ids = remove_id_generate_post_pdf( $post_id, $existing_ids );
			if ( empty( $existing_ids ) ) {
				break;
			}
		} else {
			$existing_ids = remove_id_generate_post_pdf( $post_id, $existing_ids );
        }
    }

    // Set option back to false after processing.
    update_option( 'is_generating_pdfs', false );
    echo "PDFs generated successfully for the selected posts." . "\n";
}
