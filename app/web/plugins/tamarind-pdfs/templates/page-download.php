<?php
/**
 * Template Name: New Downloads
 *
 * @package Tamarind_Pdfs
 * phpcs: disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 * phpcs: disable WordPress.Security.NonceVerification.Recommended
 */

namespace tamarind_pdfs;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! isset( $_GET['f'] ) ) {
    exit;
}

$param = sanitize_text_field( wp_unslash( $_GET['f'] ) );

$encode_decode = new Tamarind_Encoding();

if ( function_exists( 'tamarind_pdfs\get_name_dir_pdf_files' ) ) {
    $name_dir_pdf_files = get_name_dir_pdf_files();
} else {
    $name_dir_pdf_files = 'pdfs-downloads';
}

if ( '' !== $param ) {
    $fichero = '/wp-content/uploads/' . $name_dir_pdf_files . '/' . $encode_decode->decrypt( $param );
} else {
    $fichero = '/wp-content/plugins/tamarind-pdfs/assets/dummy/pdf.pdf';
}
$filepath = '/wp-content/uploads/' . $name_dir_pdf_files . '/' . $encode_decode->decrypt( $param );

if ( file_exists( $_SERVER['DOCUMENT_ROOT'] . $filepath ) ) {
    ?>
    <style>
        body {
            margin: 0;
            overflow: hidden;
        }
    </style>
    <iframe src="/wp-content/plugins/tamarind-pdfs/pdf-viewer/web/viewer.php?file=<?php echo $fichero; ?>" style="height:100%; width:100%; overflow: hidden;" title="Iframe Example"></iframe>
    <?php
} else {
    echo 'File does not exist';
}
?>
