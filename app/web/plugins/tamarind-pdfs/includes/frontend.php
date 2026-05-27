<?php
/**
 * Frontend functions for Tamarind PDFs.
 *
 * @package Tamarind_Pdfs
 */

namespace tamarind_pdfs;

defined( 'ABSPATH' ) || exit;

function getBotonPDF ( $id, $term_slug, $puedoLeer ) {

	$htmlGetPDF = "";

	$downloadable_file = get_field( 'post_full_pdf', $id );
	$pdf_generate = get_field( 'post_create_pdf', $id );

	if ( ( $downloadable_file != '' ) && $pdf_generate && contentTypeCanPdfDownload ($id ) && contentTypeActiveInPlan( $id ) ) {

		if (groupCanPdfDownload() && $puedoLeer) {
			$textButton = "Download PDF";
			$classPdfHeader = '';
			$contentAccordion = "
			<div class='options-pdf-content'>
				<div class='module-accordion-content-text'>
					<div class='buttons-pdf'>
					" . getBotonDownloadPDF($id) . "
					" . getBotonEmailPDF($id) . "
					</div>
				</div>	
			</div>";
		} else {
			$textButton = "<span class='tooltip' tooltip-data=' Upgrade your subscription plan to download this content'>Download PDF</span>";
			$classPdfHeader = ' pdf-disabled';
			$contentAccordion = "";
		}

		$htmlGetPDF = "
			<div class='options-pdf'>
				<tm-accordion id='accor1' role='region'>
					<div class='accordion-item'>
						<button 
							id='accordion-item-0-button' 
							class='menu-title options-pdf-header " . $classPdfHeader . "' 
							aria-expanded='false' 
							aria-controls='accordion-item-0-panel'
							type='button'
						>
							<div class='tm-btn btn-download'>
								" . $textButton . " 
							</div>
						</button>
						<div 
							id='accordion-item-0-panel' 
							class='menu-options' 
							role='region'
							aria-labelledby='accordion-item-0-button'
							hidden
						>
							" . $contentAccordion . "
						</div>
					</div>
				</tm-accordion>
			</div>
		";
	}
	return $htmlGetPDF;
}

function getBotonDownloadPDF ( $id ) {

	$botonPDF = '';
	$downloadable_file = get_field( 'post_full_pdf', $id );

	$upload_dir   = wp_upload_dir();
	$upload_dir   = $upload_dir['baseurl'];
	$downloadable_file = str_replace( $upload_dir . '/' . get_name_dir_pdf_files() . '/', '', $downloadable_file );

	$encodeDecode = new Tamarind_Encoding();
	$downloadable_file_encrypt = $encodeDecode->encrypt( $downloadable_file );
	$downloadable_file_url = '/downloads/?f=' . $downloadable_file_encrypt;

	$urlStadistics = "'" . get_permalink( $id ) . "'";
	$botonPDF = '<a onclick="insertdata(' . $urlStadistics . ')" href="' . $downloadable_file_url . '" class="btn-download-pdf btn-download-pdf-file" title="Download File" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i>Download File</a>';
	return $botonPDF;
}

function getBotonEmailPDF ( $id ) {

	$botonPDF = '';
	$downloadable_file = get_field( 'post_full_pdf', $id );

	$post_title = get_the_title( $id );

	$upload_dir   = wp_upload_dir();
	$upload_dir   = $upload_dir['baseurl'];
	$downloadable_file = str_replace($upload_dir . '/' . get_name_dir_pdf_files() . '/', '', $downloadable_file);

	$encodeDecode = new Tamarind_Encoding();
	$downloadable_file = '/downloads/?f=' . $encodeDecode->encrypt( $downloadable_file );

	$urlStadistics = "'" . get_permalink($id) . "'";
	// $botonPDF = '<a onclick="insertdata(' . $urlStadistics . ')" href="' . $downloadable_file . '" class="btn-download-pdf btn-download-pdf-email" title="Send by email" target="_blank"><i class="fa fa-envelope-o" aria-hidden="true"></i>Send by email</a>';

	$domain = $_SERVER['HTTP_HOST'];
	$hrefPDFtext = "https://" . $domain . $downloadable_file;

	$scriptMailTo = '
	<script>
		var subject = "' . $post_title . '";
		var body = "Find the PDF document by copying and pasting the following link into your browser:\n ' . $hrefPDFtext . '";
		var link = "mailto:?subject=" + encodeURIComponent(subject) + "&body=" + encodeURIComponent(body);
		
		document.querySelector(".mail-to-pdf").href = link;
	</script>
	';

	$botonPDF = '<a onclick="insertdata(' . $urlStadistics . ')" href="#nada" class="btn-download-pdf btn-download-pdf-email mail-to-pdf" title="Send by email" target="_blank"><i class="fa fa-envelope-o" aria-hidden="true"></i>Send by email</a>';
	$botonPDF .= $scriptMailTo;
	return $botonPDF;
}
