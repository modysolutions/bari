<?php
/**
 * Render functions for Tamarind PDFs.
 *
 * @package Tamarind_Pdfs
 */

namespace tamarind_pdfs;

defined( 'ABSPATH' ) || exit;

/**
 * Get the pattern of characters for each language.
 * 
 * @param string $lang The language to get the pattern.
 * @return string The pattern of characters.
 */
function get_pattern_chars ( $lang ) {
	// $japaneseCharsReg = '/[\x{3041}-\x{3096}\x{30A0}-\x{30FF}\x{3400}-\x{4DB5}\x{4E00}-\x{9FCB}\x{F900}-\x{FA6A}\x{2E80}-\x{2FD5}\x{FF5F}-\x{FF9F}\x{3000}-\x{303F}\x{31F0}-\x{31FF}\x{3220}-\x{3243}\x{3280}-\x{337F}]/u';	
	// más inteligible pero no elimina los signos de puntuación - sin Choan no hubiera sido posible!
	// $theContentHTML = preg_replace('/\\p{Han}+|\\p{Hiragana}+|\\p{Katakana}+/u', '', $theContentHTML);
	switch ($lang) {
		case 'japanese':
			$pattern = '/([\x{3040}-\x{309F}]+|[\x{30A0}-\x{30FF}]+|[\x{4E00}-\x{9FBF}]+|[\x{3400}-\x{4DBF}]+)/u';
			break;
		case 'chinese':
			$pattern = '/([\x{4E00}-\x{9FFF}]+|[\x{3400}-\x{4DBF}]+|[\x{20000}-\x{2A6DF}]+|[\x{2A700}-\x{2B73F}]+|[\x{2B740}-\x{2B81F}]+|[\x{2B820}-\x{2CEAF}]+|[\x{F900}-\x{FAFF}]+|[\x{2F800}-\x{2FA1F}]+)/u';
			break;
		case 'arabic':
			$pattern = '/([\x{0600}-\x{06FF}]+|[\x{0750}-\x{077F}]+|[\x{08A0}-\x{08FF}]+|[\x{FB50}-\x{FDFF}]+|[\x{FE70}-\x{FEFF}]+)/u';
			break;
		case 'hebrew':
			$pattern = '/([\x{0590}-\x{05FF}]+|[\x{FB1D}-\x{FB4F}]+)/u';
			break;
		case 'greek':
			$pattern = '/[\x{0370}-\x{03FF}]/u';
			break;
		case 'thai':
			$pattern = '/[\x{0E00}-\x{0E7F}]/u';
			break;
		case 'korean':
			$pattern = '/([\x{1100}-\x{11FF}]+|[\x{3130}-\x{318F}]+|[\x{AC00}-\x{D7AF}]+)/u';
			break;
		case 'armenian':
			$pattern = '/[\x{0530}-\x{058F}]/u';
			break;
		case 'georgian':
			$pattern = '/([\x{10A0}-\x{10FF}]+|[\x{2D00}-\x{2D2F}]+)/u';
			break;
		default:
			$pattern = '';
	}
	return $pattern;
}

/**
 * Parse the characters of a language.
 * 
 * @param string $str The string to parse.
 * @param string $lang The language to parse.
 * @return string The parsed string.
 */
function parse_lang_chars( $str , $lang ) {
	$patterns = get_pattern_chars( $lang );
    // Procesa cada coincidencia con una función de callback
    $result = preg_replace_callback( $patterns,function ( $matches ) use ( $lang ) {
        // Encapsula el texto en coincidencia con etiquetas span y usa trim() para eliminar espacios en blanco al inicio y al final
        return "<span class='lang-" . $lang . "'>" . trim($matches[0]) . "</span>";
    }, $str );
    return $result;
}

/**
 * Parse all languages.
 * 
 * @param string $str The string to parse.
 * @return string The parsed string.
 */
function parse_all_langs ( $str ) {
	$result = parse_lang_chars( $str , 'japanese' );
	$result = parse_lang_chars( $result , 'chinese' );
	$result = parse_lang_chars( $result , 'arabic' );
	$result = parse_lang_chars( $result , 'hebrew' );
	$result = parse_lang_chars( $result , 'greek' );
	$result = parse_lang_chars( $result , 'thai' );
	$result = parse_lang_chars( $result , 'korean' );
	$result = parse_lang_chars( $result , 'armenian' );
	$result = parse_lang_chars( $result , 'georgian' );
	return $result;
}

function get_base64_image ( $pathImage ) {
    $typeImage = pathinfo( $pathImage, PATHINFO_EXTENSION );
    $dataImage = file_get_contents( $pathImage );
    $base64Image = 'data:image/' . $typeImage . ';base64,' . base64_encode( $dataImage );
    return $base64Image;
}

function get_path_url_image ( $urlImage ) {
    return str_replace( wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $urlImage );
}

function get_html_content ( $post ) {

	$title = $post->post_title;
	$date  = get_the_date( get_option( 'date_format' ), $post->ID );

	// Imagen Destacada.
	$imagePostFeatured     = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
    $pathImagePostFeatured = get_path_url_image( $imagePostFeatured[0] );

	$specialContent = get_field( 'special_content_pdf', $post->ID );
	if ( $specialContent ) {
		if ( get_field( 'special_content_pdf_text', $post->ID ) != '' ) {
			$theContentHTML = get_field( 'special_content_pdf_text', $post->ID );
		}
		if ( get_field( 'special_content_pdf_gallery', $post->ID ) != '' ) {
			$galleryImgs = get_field( 'special_content_pdf_gallery', $post->ID );

			foreach ($galleryImgs as $img) {
				$imageGallery = $img['url'];
				$pathImageGallery = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $imageGallery);
                $base64ImageGallery = get_base64_image( $pathImageGallery );

				if ( $img['width'] < $img['height'] ) {
					$theContentHTML .=  "<div class='dina4-img'><img src='" . $base64ImageGallery . "' height='100%' width='auto'/></div>";
				} else {
					$theContentHTML .=  "<div class='dina4-img'><img src='" . $base64ImageGallery . "' width='100%' height='auto'/></div>";
				}
                if ( $img != end( $galleryImgs ) ) {
                    $theContentHTML .= "<div style='width: 0; height: 0; page-break-before: always;' pagebreak='true'></div>";
                }
			}
		}
	} else {
        $toc = '';
		$hayToc = false;
        $new_content = $post->post_content;
        if ( has_shortcode( $post->post_content, 'toc') ) {
            $toc = get_field( 'res_tocs', $post->ID );
            $toc .= '<div style="width: 0; height: 0; page-break-before: always;" pagebreak="true"></div>';
            $new_content = str_replace( '[toc]', $toc, $post->post_content );
			if ( strpos( $post->post_content, '[toc]' ) !== false ) {
				$hayToc = true;
			}
        }
		$theContentHTML = apply_filters( 'the_content', $new_content );
	}

	// eliminar espacios en blanco con &nbsp; que no detecta DOMXPath
	$theContentHTML = str_replace( '&nbsp;', ' ', $theContentHTML );
	$theContentHTML = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $theContentHTML );
	$theContentHTML = preg_replace( '#<noscript(.*?)>(.*?)</noscript>#is', '', $theContentHTML );
	$theContentHTML = str_replace( 'class="tm10 tm11 tm12 custom-table"', '', $theContentHTML );

	$theContentHTML = parse_all_langs( $theContentHTML );

	$theContentHTML = str_replace( '<!--more-->', '<div style="width: 0; height: 0; page-break-before: always;" pagebreak="true"></div>', $theContentHTML );
	
	$docPDF = new \DOMDocument();
	// @$docPDF es para suprimir el warning que aparece
	@$docPDF->loadHTML( '<?xml encoding="utf-8" ? ><div id="contenido-pdf">' . $theContentHTML . '</div>' );

	$xpath = new \DOMXPath( $docPDF );

	$ps = $xpath->query( '//p' );
	foreach ( $ps as $p ) {
		if ( $p->nodeValue == ' ' ) {
			$p->parentNode->removeChild( $p );
		}
	}
	$docPDF->savehtml();

	$aes = $xpath->query( '//a' );
	foreach ( $aes as $a ) {
		$esImagen = false;
		foreach ( $a->childNodes as $child ) {
			if ( $child->nodeName == 'img' ) {
				$src      = $child->attributes->getNamedItem( 'src' )->value;
				$width    = $child->attributes->getNamedItem( 'width' )->value;
				$height   = $child->attributes->getNamedItem( 'height' )->value;
				$esImagen = true;
			}
		}

		if ( $esImagen ) {
			$nuevoNodo = $docPDF->createElement( 'img' );
			$nuevoNodo->setAttribute( 'class', 'new-image-de-link' );
			$nuevoNodo->setAttribute( 'src', $src );
			$nuevoNodo->setAttribute( 'width', $width );
			$nuevoNodo->setAttribute( 'height', $height );
			$a->parentNode->replaceChild( $nuevoNodo, $a );
		}
	}
	$docPDF->savehtml();

	$tables = $xpath->query( '//table' );
	foreach ( $tables as $table ) {
		$nuevoNodo = $docPDF->createElement( 'div' );
		$nuevoNodo->setAttribute( 'class', 'div-table' );
		$tableAux = $table->cloneNode( true );
		$nuevoNodo->appendChild( $tableAux );

		$table->parentNode->replaceChild( $nuevoNodo, $table );
	}
	$docPDF->savehtml();

	$tables = $xpath->query( '//table/tbody/tr/td' );
	foreach ( $tables as $table ) {
		$table->removeAttribute( 'width' );
	}
	$docPDF->savehtml();

	// con subhomepage snignifica que miro si la primera imagen del contenido es igual a la imagen destacada para no mostrarla y solo mostrar la imagen destacada
	$subhomepage = false;
	$base64ImagePost = '';

	if ($subhomepage) {
		$imgPortada = '';
		$imgs = $docPDF->getElementsByTagName( 'img' );
		for( $i = $imgs->length; --$i >= 0; ){
			$node = $imgs->item( $i );
			$imgSrc = $node->getAttribute( 'src' );

			if ( ( $i == 0 ) && ( $imagePostFeatured[0] == $imgSrc ) ) {
				$node->parentNode->removeChild( $node );
			} else {
                $pathImagePost = get_path_url_image( $imgSrc );
                $base64ImagePost = get_base64_image( $pathImagePost );
				$node->setAttribute( 'src', $base64ImagePost );			
                $node->setAttribute( 'width', 'auto' );
                $node->setAttribute( 'height', 'auto' );
								
				if ( $i == 0 && !$specialContent ) {
					$imgPortada = $base64ImagePost;
					$node->parentNode->removeChild( $node );
				} else {
					if ( ! $specialContent ) {
						$node->setAttribute( 'style', 'max-width: 670px; max-height: 500px; margin: 50px auto;' );
					} else {
						$node->setAttribute( 'style', 'max-width: 100%; max-height: 100%;' );
					}
				}
			}	
		}
	} else {
		$imgPortada = '';
		$imgs = $docPDF->getElementsByTagName( 'img' );
		for( $i = $imgs->length; --$i >= 0; ){
			$node = $imgs->item( $i );

			$imgSrcSet = $node->getAttribute( 'srcset' );

            if ( $imgSrcSet != '' ) {
				$urls = explode( ', ', $imgSrcSet );
                $imgMaxSrc = get_url_max_size_from_srcset( $urls );
                $pathImagePost = get_path_url_image( $imgMaxSrc );
			} else {
				// solo hay un SRC
				$imgSrc = $node->getAttribute( 'src' );
                $pathImagePost = get_path_url_image( $imgSrc );
			}

            $base64ImagePost = get_base64_image( $pathImagePost );
			$node->setAttribute( 'src', $base64ImagePost );

			if ( ( $i == 0 ) && $hayToc ) {
				$newNode = $docPDF->createElement( 'div' );
				$newNode->setAttribute( 'class', 'background-first-image-container' );
				$newNode->setAttribute( 'style', 'background-image: url(' . $base64ImagePost . ');' );
				$parentNode = $node->parentNode;
				$parentNode->removeChild( $node );
				$parentNode->appendChild( $newNode );	
			} else {
				$node->setAttribute( 'width', '100%' );
				$node->setAttribute( 'height', 'auto' );
				if (!$specialContent) {
					$node->setAttribute( 'style', 'max-width: 634px; margin: 20px auto;' );
				} else {
					$node->setAttribute( 'style', 'max-width: 100%; max-height: 100%;' );
				}
			}
		}
	}
	$content = $docPDF->savehtml();

	$contentFooter = get_field( 'common_footer_downloads', 'options' );
	$contentFooterLinks = get_field( 'common_footer_links_downloads', 'options' );

    if ( function_exists( 'get_field' ) ) {
        $fileImage = get_field( 'pdf_home_image', 'options' );
        $pathHomeImage = get_path_url_image( $fileImage );
    } else {
        $pathHomeImage = get_stylesheet_directory() . '/images/pdf_home.jpg';
    }
    $base64Image = get_base64_image( $pathHomeImage );

	if ( file_exists( $pathImagePostFeatured ) ) {
        $base64ImagePost = get_base64_image( $pathImagePostFeatured );
		$imageHtmlPost = "<img class='image-home' src='" . $base64ImagePost . "' width='670'/>";
	} else {
		if ( $imgPortada != '' ) {
			$imageHtmlPost = "<img class='image-home' src='" . $imgPortada . "' width='670'/>";
		} else {
			$imageHtmlPost = '';
		}
	}

	$imageFooter = get_field( 'common_footer', 'options' );
    $pathImageFooter = get_path_url_image( $imageFooter );
    $base64ImageFooter = get_base64_image( $pathImageFooter );

	$colorPrimary = '#28285b';
	$colorBox = '#6d64b6';
	$colorLink = '#898fde';
	$colorH3 = '#28285b';
	$colorH4 = '#898fde';

    if ( function_exists ( 'get_field' ) ) {
        $colorPrimary = get_field( 'field_pdf_primary_color', 'options' );
        $colorBox = get_field( 'field_pdf_date_color', 'options' );
        $colorLink = get_field( 'field_pdf_link_color', 'options' );
        $colorH3 = get_field( 'field_pdf_title_h3_color', 'options' );
        $colorH4 = get_field( 'field_pdf_title_h4_color', 'options' );
    }

	$html = "
			<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'
			'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
			<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'><head>
			<link rel='preconnect' href='https://fonts.googleapis.com'>
        	<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
			<link href='https://fonts.googleapis.com/css2?family=Bitter:ital,wght@0,100;0,200;0,400;0,700;0,900;1,400;1,700&family=IBM+Plex+Sans+Arabic:wght@100;200;300;400;500;600;700&family=Noto+Sans+Armenian:wght@100..900&family=Noto+Sans+Georgian:wght@100..900&family=Noto+Sans+Hebrew:wght@100..900&family=Noto+Sans+JP:wght@100..900&family=Noto+Sans+KR:wght@100..900&family=Noto+Sans+SC:wght@100..900&family=Noto+Sans+TC:wght@100..900&family=Noto+Sans+Thai:wght@100..900&family=Noto+Sans:wght@100..900&display=swap' rel='stylesheet'>
			<title></title>

			<style type='text/css'>

			@page {
				size: A4;
				margin: 80px;
				background: #000;
			}
			@page :first {
				margin: 0;
			}

    		body { 
				background-color: #fff; 
				font-family: 'Bitter', sans-serif, times;
				font-weight: 400;	
			}

			.lang-japanese {
				font-family: 'Noto Sans JP', sans-serif;
				font-weight: 400;
				font-style: normal;
			}

			.lang-arabic {
				font-family: 'IBM Plex Sans Arabic', sans-serif;
				font-weight: 400;
				font-style: normal;
			}

			.lang-chinese {
				font-family: 'Noto Sans SC', sans-serif;
				font-weight: 400;
				font-style: normal;
			}

			.lang-hebrew {
				font-family: 'Noto Sans Hebrew', sans-serif;
				font-weight: 400;
				font-style: normal;
			}

			.lang-greek {
				font-family: 'Noto Sans', sans-serif;
				font-weight: 400;
				font-style: normal;
			}

			.lang-thai {
				font-family: 'Noto Sans Thai', sans-serif;
				font-weight: 400;
				font-style: normal;
			}

			.lang-korean {
				font-family: 'Noto Sans KR', sans-serif;
				font-weight: 400;
				font-style: normal;
			}

			.lang-armenian {
				font-family: 'Noto Sans Armenian', sans-serif;
				font-weight: 400;
				font-style: normal;
			}

			.lang-georgian {
				font-family: 'Noto Sans Georgian', sans-serif;
				font-weight: 400;
				font-style: normal;
			}

			.entry-content p a, u {
				color: " . $colorLink . ";
				text-decoration: underline;
			}

			/* Estilos para el encabezado de la primera página */
			.pdf-homepage {
				background-image: url('" . $base64Image . "');
				background-size: cover;
				height: 1122px;
				width: 794px;
				page-break-after: always;
			}

			.page-header-title {
				padding-top: 900px;
				padding-left: 125px;
				padding-right: 125px;
				color: #fff;
				font-size: 28px;
				text-align: left;
				font-weight: 700;
				line-height: 28px;
			}

			.page-header-date {
				padding-left: 125px;
				padding-right: 125px;
				padding-top: 10px;
				color: ".$colorBox.";
				font-size: 16px;
				text-align: left;
				font-weight: 400;
			}

			.pdf-subhomepage {
				background: ".$colorPrimary.";
				padding-top: 10px;
				page-break-after: always;

			}

			.pdf-subhomepage-img {
				background-image: url('" . $base64ImagePost . "');
				background-size: cover;
				margin-left: 10px;
				margin-right: 10px;
				height: 464px;
				width: 614px;
				margin-top: 0px;
			}

			.pdf-subhomepage-title {
				width: 553px;
				background: ".$colorPrimary.";
				padding-top: 20px;
				padding-bottom: 20px;
				padding-left: 40px;
				padding-right: 40px;
			}

			.pdf-subhomepage-txt {
				color: #fff;
				font-size: 22px;
				line-height: 22px;
				text-align: left;
				font-weight: 700;
				
			}

			.pdf-content {
				height: 962px;
				width: 634px;
				color: #333;
				// border: 1px solid red;
			}

			.pdf-content-inner {
				width: 634px;
			}

			.pdf-content-inner a {
				color: ".$colorLink.";
				text-decoration: underline;
			}

			.pdf-final {
				height: 962px;
				width: 634px;
				// border: 1px solid red;
				page-break-before: always;
			}

			.page-footer-img {
				width: 634px;
			}

			.page-footer-content h2 {
				background: ".$colorPrimary.";
				color: #fff;
				font-family: Bitter,serif;
				font-weight: 400;
				font-size: 20px;
				line-height: 20px;
				padding: 15px;
			}

			.page-footer-content {
				word-wrap: break-word;
				color:#666;
				font-size: 14px;
				line-height: 16px;
				font-family: Bitter,serif;
				font-weight: 400;
			}

			.page-footer-links {
				border-top: 2px solid rgba(40, 40, 90, 0.1);
				word-wrap: break-word;
				color:#666;
				font-size: 14px;
				line-height: 16px;
				padding-top: 10px;
				font-family: Bitter,serif;
				font-weight: 400;
			}

			.page-footer-links a {
				color: ".$colorLink.";
				font-family: Bitter,serif;
				font-weight: 700;
				padding-top: 0px;
				text-decoration: underline;
			}

			.page-footer-links ul {
				list-style: none;
				padding: 0;
				margin: 0;
			}
			.page-footer-links li {
				margin-top: 5px;
			}

			.page-footer-links li a {
				font-size: 14px;
				margin: 0;
				padding: 0;
			}

			/* Estilos para el contenido del documento */
			.contenido {
				margin: 0;
				border: 1px solid green;
			}

			.el-contenido {
				margin-top: 250px;
				margin-bottom: 150px;
				margin-left: 150px;
				margin-right: 150px;
				width: 570px;
				height: 700px;
				border: 1px solid red;
			}

			/* content */
			.div-table {
				page-break-inside:avoid !important;
				margin-top: 50px;
				margin-bottom: 50px;
			}

			.content-body table {
				width: 670px;
				border: 1px solid #eee;
				page-break-inside: avoid !important;
				border-collapse: collapse;
				
			}

			.content-body table tbody tr {
				border: 1px solid #eee;
			}

			.content-body table tbody tr:nth-child(odd){
				background-color: #eee;
			  }

			.content-body table td {
				border-top: 0;
				border-left: 0;
				border-right: 0;
				border-bottom: 0px solid #ccc;
				padding: 15px;
				max-width: 300px;
				font-size: 14px;
			}

			.content-body .image-home {
				page-break-after: always;
			}

			.div-table .tm9 td.tm10, .div-table .tm9 tr:nth-child(2) td:first-child {
				position: relative;
				padding-right: 300px;
			}
			
			.div-table .tm9 td.tm10 img, .div-table .tm9 tr:nth-child(2) td:first-child img {
				position: absolute;
				right: 0;
				top: 0;
				margin: 10px !important;
				max-width: 250px!important;
			}

			.content-body ul li img {
				max-width: 100px !important;
				margin: 0 !important;
			}

			.pdf-content p img {
				display: block;
			}

			.pdf-content h3 {
				color: ".$colorH3.";
			}

			.pdf-content h4 {
				color: ".$colorH4.";
			}
			
			.background-first-image-container {
				width: 634px;
				height: 300px;
				background-size: cover;
				background-position: center;
				overflow: hidden;
				margin-top: 10px;
				margin-bottom: 10px;
			}

			</style>
			</head>
			<body>

			<div class='pdf-homepage'>
				<div class='page-header-title'>" . $title . "</div>
				<div class='page-header-date'>" . $date . "</div>
			</div>


			<div class='pdf-content'>
				<div class='pdf-content-inner content-body'>" . $content . "</div>			
			</div>

			<div class='pdf-final'>
				<div class='page-footer-img'><img src='" . $base64ImageFooter . "' width='634' /></div>
				<div class='page-footer-content'>" . $contentFooter . "</div>
				<div class='page-footer-links'>" . $contentFooterLinks . "</div>
			</div>

			</body>
			</html>
			";
			
	return $html;
}


/**
 * Get URL maxi size from srcset.
 * 
 * @param array $urls The URLs to get the max size.
 * @return string The URL with the max size.
 */
function get_url_max_size_from_srcset ( $urls ) {
    $sizes = [];
    foreach ( $urls as $entry ) {
        if ( preg_match( '/(.+?)\s(\d+)w$/', $entry, $matches ) ) {
            $url = trim( $matches[1] );
            $size = (int)$matches[2]; 
            $sizes[$size] = $url;
        }
    }
    $max_size = max( array_keys( $sizes ) );
    return $sizes[$max_size];
}
