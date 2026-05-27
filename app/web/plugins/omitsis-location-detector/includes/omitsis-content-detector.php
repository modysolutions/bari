<?php
/**
 * Location Detector
 *
 * @package OmitsisLocationDetector
 */

namespace omitsis\content_detector;

defined( 'ABSPATH' ) || exit;

function china_active () {
    $active = false;
    $active = get_field('china_active', 'option');
    return $active;
}

// function get $post->ID from permalink
// function get_post_id_by_permalink($permalink) {
//     global $wpdb;
//     $post_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '" . $permalink . "'");
//     return $post_id;
// }


// CHINA POPUP VIDEO FUNCTIONS
function show_china_popup_video ( $post_id ) {
    $show = false;
    $china_v_visibility = get_field('china_v_visibility', 'option');
    if (is_array($china_v_visibility)) {
        $show = in_array($post_id, $china_v_visibility);
    } else {
        $show = ($post_id == $china_v_visibility) ? true : false;
    }
    return $show;
}

function china_popup_video ( $post_id ) {
    
    if ( china_active() && show_china_popup_video ( $post_id ) ) {
        $china_v_title = get_field('china_v_title', 'option');
        $china_v_subtitle = get_field('china_v_subtitle', 'option');

        $china_v_media = get_field('china_v_media', 'option');
        $mediaType = $china_v_media['media_type'];
        $media_is_gallery = $china_v_media['media_is_gallery'];

        if ($mediaType) {
            $mediaIframe = $china_v_media['media_video_iframe'];
            $mediaVideo = $china_v_media['media_video'];
            // $mediaPoster = $china_v_media['media_video_poster_image'];
        } else {
            if ($media_is_gallery) {
                $media = $china_v_media['media_gallery'];
            } else {
                $media = $china_v_media['media_image'];
                $mediaMobile = $china_v_media['media_image_mobile'];

                if ($mediaMobile == '') {
                    $mediaMobile = $media;
                }
            }
        }

        ob_start(); //Inicio del buffer
        ?>
        <dialog id="geo-video-china-modal" class="tm-modal">
            <div class="modal-geo tm-modal-content">
                <button class="tm-modal-close" type="button">×</button>
                <?php
                if ($china_v_title && $china_v_title!="") {
                    echo '<h3 class="geo-title">' . $china_v_title . '</h3>';
                }
                if ($china_v_subtitle && $china_v_subtitle!="") {
                    echo '<div class="geo-subtitle">' . $china_v_subtitle . '</div>';
                }

                echo '<div class="geo-image">';
                if ($mediaType) {
                    if ($mediaVideo != '') {
                        ?>
                        <video class="webinar-video-embed" width="100%" height="auto" src="<?php echo $mediaVideo['url'];?>"  playsinline loop autoplay muted >
                        <?php 
                    } else {
                        if ($mediaIframe != '') {
                            echo $mediaIframe;
                        }
                    } 
                } else {
                    if ($media_is_gallery) {

						if ( $media ) {
							$unique_id = 'gallery-' . uniqid();
							$totalpictures = count( $media );
							?>
							<div class="swiper-container picture-nomargintop-mobile">
								<div class="swiper" id="<?php echo esc_attr( $unique_id ); ?>">
									<div class="swiper-wrapper tour-slideshows">
										<?php
										foreach ( $media as $image ) {
											?>
											<div class="swiper-slide">
												<img src="<?php echo esc_url( $image['url'] ); ?>" 
													alt="<?php echo esc_attr( $image['alt'] ?: '' ); ?>" 
													data-no-lazy="1" />
											</div>
											<?php
										}
										?>
									</div>
								</div>

								<?php if ( $totalpictures > 1 ) { ?>
									<div class="swiper-pagination swiper-pagination-blue" data-swiper-target="<?php echo esc_attr( $unique_id ); ?>"></div>
								<?php } ?>
							</div>

							<?php
							// Inicialize Swiper.
							if ( $totalpictures > 1 ) {
								echo call_swiper_slider(
									$unique_id,
									1,
									false,
									true,
									array(
										'spaceBetween' => 0,
										'loop' => true,
										'speed' => 600,
										'effect' => 'slide',
										'autoplay' => false,
									)
								);
							} else {
								echo call_swiper_slider(
									$unique_id,
									1,
									false,
									false,
									array(
										'spaceBetween' => 0,
										'loop' => false,
										'enabled' => false,
									)
								);
							}
						}

                    } else {
                        ?>    
                        <div class="china-image">
                            <img src="<?php echo $media['url']; ?>" alt="<?php echo $media['alt']; ?>">
                        </div>
                        <?php  
                    }
                }
                echo '</div>';
                ?>
            </div>
		</dialog>
        <?php
        $html = ob_get_clean(); //Final del buffer y guardado en $html
        return $html;
    } else {
        return false;
    }
    
}

// CHINA POPUP FORM FUNCTIONS
function get_content_type_ids ( $post_id ) {

    $taxonomy = 'content_types';
    $terms = get_the_terms($post_id, $taxonomy);
    // new array to store all the ids
    $ids = array();
    if (is_array($terms)) {
        foreach ($terms as $term) {
            $ids[] = $term->term_id;
        }
    }
    return $ids;
}

function show_china_popup_form ( $post_id ) {
    $show = false;
    $china_f_visibility = get_field('china_f_visibility', 'option');
    $ids = get_content_type_ids( $post_id );
    if (is_array($china_f_visibility)) {
        $array_intersect = array_intersect($ids, $china_f_visibility);
        $show = (count($array_intersect) > 0) ? true : false;
    } 
    return $show;
}

function china_popup_form ( $post_id ) {
    if ( china_active() && show_china_popup_form( $post_id ) ) {
        ob_start(); //Inicio del buffer
        ?>
        <dialog id="geo-form-china-modal" class="tm-modal">
            <div class="modal-geo tm-modal-content">
                <button class="tm-modal-close" type="button">×</button>
                <?php 
                    if ( is_plugin_active( 'tamarind-forms/tamarind-forms.php' ) ) {
                        \tamarind_forms\display_form\display_form( 'china_form', true );
                    }
                ?>
            </div>
		</dialog>
        <?php
        $html = ob_get_clean(); //Final del buffer y guardado en $html
        return $html;
    } else {
        return false;
    }
    
}

function china_popup_form_download ( $post_id ) {
    if ( china_active() && show_china_popup_form( $post_id ) ) {
        $ids = get_content_type_ids( $post_id );

        // for each content type, get the download link
        $china_f_download = '';
        foreach ($ids as $id) {
            $china_f_download = get_field('free_pdf_for_china', 'content_types_' . $id)['url'];
        }

        return $china_f_download;
    } else {
        return false;
    }
}

// CHINA WIDGET FUNCTIONS
function china_widget () {
    
    if (china_active()) {
        $china_w_title = get_field('china_w_title', 'option');
        $china_w_icon = get_field('china_w_icon', 'option');
        $china_w_link = get_field('china_w_link', 'option');

        $china_w_qr_image = get_field('china_w_qr_image', 'option');
        $china_w_qr_icon = get_field('china_w_qr_icon', 'option');
        $china_w_qr_link = get_field('china_w_qr_link', 'option');

        ob_start(); //Inicio del buffer

        if ($china_w_qr_link && $china_w_qr_link!="") {
            ?>
            <div id="geo-widget-china-qr" class="geo-widget-china geo-widget-china-qr">
                <?php 
                if ($china_w_qr_link && $china_w_qr_link!="") { ?>
                    <div class="china-widget-qr">
                        <a href="<?php echo $china_w_qr_link['url']; ?>" target="_blank">
                            <img src="<?php echo $china_w_qr_image['url']; ?>" alt="<?php echo $china_w_qr_image['alt']; ?>">
                        </a>
                    </div>
                    <?php
                }                
                ?>

                <?php
                if ($china_w_qr_icon && $china_w_qr_icon!="") {
                    ?>    
                    <div class="china-widget-icon">
                        <img src="<?php echo $china_w_qr_icon['url']; ?>" alt="<?php echo $china_w_qr_icon['alt']; ?>">
                    </div>
                    <?php 
                }
                ?>
            </div>
            <?php
        }
        ?>
        <div id="geo-widget-china" class="geo-widget-china">
            <?php
            if ($china_w_icon && $china_w_icon!="") {
                ?>    
                <div class="china-widget-icon">
                    <img src="<?php echo $china_w_icon['url']; ?>" alt="<?php echo $china_w_icon['alt']; ?>">
                </div>
                <?php 
            }
            ?>
            <?php 
            if ($china_w_link && $china_w_link!="") {
                echo '<div class="china-widget-link">';
                echo '<a href="' . $china_w_link['url'] . '" target="_blank">' . $china_w_title . '<i class="fa fa-plus-circle" title="Read more"></i></a>';
                echo  '</div>';
            }                
            ?>
        </div>
        <?php
        $html = ob_get_clean(); //Final del buffer y guardado en $html
        return $html;
    } else {
        return false;
    }
    
}

// CHINA DUMMY
function china_dummy () {
    if (china_active()) {
        ?>
        <div class="china-dummy"></div>
        <?php
    }
}
