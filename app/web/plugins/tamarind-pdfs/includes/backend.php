<?php
/**
 * Backend functions for Tamarind PDFs.
 *
 * @package Tamarind_Pdfs
 */

namespace tamarind_pdfs;

use function tamarind_subscriptions\subscription_plan\{get_data_plan};
use function tamarind_subscriptions\access\{get_type_access_roles,is_role};

defined( 'ABSPATH' ) || exit;

function contentTypeCanPdfDownload( $postId ) {
	$administrative_roles = get_type_access_roles( 'roles_administrative_access' );
	if ( is_role( $administrative_roles ) ) {
		return true;
	}
	// por defecto se pueder descargar a excepción que alguna de los content_types del post no lo permita.
	$canDownload = true;

	$taxonomy = 'content_types';
	$terms = get_the_terms( $postId, $taxonomy );

	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			// miramos si a nivel de content type tiene activado o no la descarga de pdf.
			if (get_field( 'allow_pdf_download_content_type', $term->taxonomy . '_' . $term->term_id ) == false ) {
				$canDownload = false;
			}
		}
	}
	return $canDownload;
}

function contentTypeActiveInPlan( $post_id ) {
	$administrative_roles = get_type_access_roles( 'roles_administrative_access' );
	if ( is_role( $administrative_roles ) ) {
		return true;
	}
	// por defecto se pueder descargar a excepción que alguna de los content_types del post no lo permita.
	$data_plan = get_data_plan();

	if ( empty( $data_plan ) || ! isset( $data_plan['plan_contents'] ) ) {
		return false;
	}

	$taxonomy = 'content_types';
	$terms = get_the_terms( $post_id, $taxonomy );

	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( ! in_array( $term->term_id, $data_plan['plan_contents'] ) ) {
				return false;
			}
		}
	}
	return true;
}

function groupCanPdfDownload() {
	$administrative_roles = get_type_access_roles( 'roles_administrative_access' );
	if ( is_role( $administrative_roles ) ) {
		return true;
	}
	$dataPlan = get_data_plan();
	$allowPdfDownloads = $dataPlan['plan_allow_pdf_downloads'];

	return $allowPdfDownloads;
}

/**
 * Check if the taxonomy is for PDF download. This function is used in functions-single.php
 *
 * @param array $terms Array of terms.
 * @return bool
 */
function is_taxonomy_for_pdf_download( $terms ) {
	$terms_for_pdf_download = get_field( 'trial_pdf_downloads', 'option' );
	foreach ( $terms as $term ) {
		if ( in_array( $term->term_id, $terms_for_pdf_download ) ) {
			return true;
		}
	}
	return false;
}
