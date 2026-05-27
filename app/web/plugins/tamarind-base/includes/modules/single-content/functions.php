<?php
/**
 * Functions for single content
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\single_content;

use WP_Query;
use function tamarind_subscriptions\access\{current_user_can_read_post, is_content_type_open_content, get_post_terms_content_type};
use function tamarind_subscriptions\subscription_plan\{get_data_plan, is_alerts_plan};
use function tamarind_base\{print_post_list};

function getVisibility() {
	$puedoLeer = current_user_can_read_post();

	$visibility = 'visitor';
	if ( $puedoLeer ) {
		// si puedo leer muestra sidebar de suscriptor
		$visibility = 'subscriber';

		$open_content_all_web = is_content_type_open_content();
		if ( $open_content_all_web ) {
			// contenido abierto para todos
			if ( ! is_user_logged_in() ) {
				// si no estas loginado muestra el de visitante en lugar del de suscriptor
				$visibility = 'visitor';
			}
		}
	} else {
		// el contenido es para visitantes o para suscriptores que no pueden leer este post según su plan de Suscripción
		if ( ! is_user_logged_in() ) {
			// si no estas loginado muestra el de visitante
			$visibility = 'visitor';
		} else {
			// si estas loginado muestra el de suscriptor sin poder leer el contenido (limited Plan)
			$visibility = 'limited-plan';
		}
	}
	return $visibility;
}

function printCtaModal( $free_sample = false ) {
	global $post;
	$puedoLeer = current_user_can_read_post();

	if ( $free_sample ) {
		$button_text = get_field( 'r_cta_main_full_free_sample', 'option' );
		$form_title = get_field( 'r_cta_main_full_free_sample_title', 'option' );
	} else {
		$button_text = get_field( 'r_cta_main_full_pdf', 'option' );
		$form_title = get_field( 'r_cta_main_full_pdf_title', 'option' );
	}

	?>
	<a href="#free-sample-modal" class="btn-primary tm-modal-trigger" title="<?php echo esc_html( $button_text ); ?>">
		<?php echo esc_html( $button_text ); ?>
	</a>
	<dialog id="free-sample-modal" class="tm-modal">
		<div class="tm-modal-content">
				<button class="tm-modal-close" type="button">×</button>
				<?php
				if ( ! $puedoLeer ) {
					if ( is_plugin_active( 'tamarind-forms/tamarind-forms.php' ) ) {
						$param_download_form = $free_sample ? 'free_sample' : 'download_pdf';
						\tamarind_forms\display_form\display_download_form( $param_download_form, $post->ID );
					}
				}
				?>
		</div>
	</dialog>
	<?php
	return true;
}

function getFreeSample() {
	if ( ! is_user_logged_in() ) {
		$free_sample_available = get_field( 'res_freesamp' );
		if ( $free_sample_available && $free_sample_available != '' ) {
			$free_sample = true;
			printCtaModal( $free_sample );
			return true;
		}
	}
	return false;
}

function printDefaultRestrictedContent() {
	$message = get_field( 'r_cta_restricted_content_message', 'option' );
	$cta = get_field( 'r_cta_restricted_content_cta', 'option' );

	echo '<div class="tm-restricted-content tm-layout-grid tm-layout-grid--large">';
	if ( $cta && $cta != '' ) {
		echo '<div class="tm-restricted-content__cta">';
		echo '<a href="' . esc_url( $cta['url'] ) . '" target="' . esc_attr( $cta['target'] ) . '" class="tm-btn btn-default">' . esc_html( $cta['title'] ) . '</a>';

		if ( ! is_user_logged_in() ) {
			$textButton = get_field( 'r_cta_secoundary_button', 'option' );
			?>
			<div class="tm-restricted-content__cta--secoundary">
				<a href="#login-modal" class="tm-btn btn-link tm-modal-trigger"><?php echo $textButton; ?></a>
			</div>
			<?php
		}

		echo '</div>';
	}
	if ( $message && $message != '' ) {
		echo '<div class="tm-restricted-content__message">' . $message . '</div>';
	}
	echo '</div>';

	return true;
}

function showDefaultRestrictedContent() {
	$show_default_restricted_content = get_field( 'enable_default_restricted_contents', 'option' );
	if ( $show_default_restricted_content ) {
		return true;
	}
	return false;
}

// Contenido central
function printRestrictedContent() {
	global $post;
	?>
	<div class="restricted-content-ctas">
		<?php
		if ( showDefaultRestrictedContent() ) {
			printDefaultRestrictedContent();
		} else {
			?>
			<div class="restricted-content-ctas-main">
				<?php
				// Primer caso: el post tiene asociado en File como 'Free Sample'
				if ( ! getFreeSample() ) {
					// Segundo caso: el content type permite hacer descarga del full PDF
					$terms = get_the_terms( $post->ID, 'content_types' );
					$show_full_pdf = false;
					if ( function_exists( '\tamarind_pdfs\is_taxonomy_for_pdf_download' ) ) {
						if ( \tamarind_pdfs\is_taxonomy_for_pdf_download( $terms ) ) {
							printCtaModal();
							$show_full_pdf = true;
						}
					}
					if ( ! $show_full_pdf ) {
						// Tercer caso: default
						$mainLink = get_field( 'r_cta_main', 'option' );
						?>
						<a href="<?php echo $mainLink['url']; ?>" target="<?php echo $mainLink['target']; ?>" class="btn-primary">
							<?php echo $mainLink['title']; ?>
						</a>
						<?php
					}
				}
				?>
			</div>
			<?php
			$buy_version_available_product = get_field( 'tracker_db_purchase_version' );
			$buy_version_available = get_field( 'purchase_version' );

			if ( $buy_version_available_product && $buy_version_available ) {
				?>
				<div class="restricted-content-get-report">
					<a href="<?php echo get_permalink( $buy_version_available_product ); ?>" class="cta-btn cta-get-report cta-btn-medium">
						BUY THIS PRODUCT <i class="fa fa-shopping-cart"></i>
					</a>
				</div>
				<?php
			}

			if ( ! is_user_logged_in() ) {
				$textButton = get_field( 'r_cta_secoundary_button', 'option' );
				?>
				<div class="restricted-content-ctas-secoundary">
					<a href="#login-modal" class="btn-secondary tm-modal-trigger"><?php echo $textButton; ?></a>
				</div>
				<?php
			}
		}
		?>
	</div>
	<?php
	return true;
}

function printNewsletterForm() {
	ob_start();
	?>
	<div class="formulario-newsletter-inicio">
		<div class="formulario-newsletter">
			<?php
			if ( is_plugin_active( 'tamarind-forms/tamarind-forms.php' ) ) {
				\tamarind_forms\display_form\display_form( 'newsletter_post_form', true, get_the_id() );
			}
			?>
			<div class="formulario-newsletter-logo"></div>
		</div>
	</div>
	<?php
	$htmlFormNews = ob_get_contents();
	ob_end_clean();
	return $htmlFormNews;
}

function getBottomContentVisibility( $visibility ) {
	$term_ids = get_post_terms_content_type( get_the_ID(), 'term_id' );

	$condicionVisibility = false;
    $visibilityDefault = array();

	if ( have_rows( 'single_bottom_content_settings', 'options' ) ) {
		while ( have_rows( 'single_bottom_content_settings', 'options' ) ) {
			the_row();

			$widgetDefault = get_sub_field( 'bottom_content_setting_default' );
			$widgetVisibility = get_sub_field( 'select_setting_visibility' );

			if ( in_array( $visibility, $widgetVisibility ) ) {
				if ( $widgetDefault ) {
					$visibilityDefault = get_sub_field( 'select_setting_widget' );
				} else {
					if ( ! $condicionVisibility ) {
						$contentTypes = get_sub_field( 'bottom_content_setting_content_type' );
						$widgets = get_sub_field( 'select_setting_widget' );

						foreach ( $contentTypes as $contentType ) {
							if ( in_array( $contentType->term_id, $term_ids ) ) {
								$condicionVisibility = true;
								return $widgets;
							}
						}
					}
				}
			}
		}
	}

	if ( ! $condicionVisibility ) {
		return $visibilityDefault;
	}

	return false;
}

function printContentBottom( $visibility ) {
	$contentBottom = getBottomContentVisibility( $visibility );
	$primero = true;

	if ( in_array( 'author', $contentBottom ) ) {
		$authorId = get_the_author_meta( 'ID' );
		printAuthorCard( $authorId );
		$primero = false;
	}
	if ( in_array( 'benefits', $contentBottom ) ) {
		if ( ! $primero ) {
			printSeparator();
		}
		print_widget_benefits();
		$primero = false;
	}

	// devuelvo qué módulos se deben mostrar de los relacionados
	return $contentBottom;
}

function printAuthorCard( $userId ) {

	// gest display name from user id
	$authorCardName = get_the_author_meta( 'display_name', $userId );
	$authorCardRole = get_field( 'author_card_role', 'user_' . $userId );
	$authorCardPicture = get_field( 'author_card_picture', 'user_' . $userId );
	$authorCardDescription = get_field( 'author_card_description', 'user_' . $userId );

	?>
	<div class="author-signature">
		<picture class="author-picture">
			<?php
			if ( $authorCardPicture && $authorCardPicture != '' ) {
				?>
				<img src="<?php echo $authorCardPicture['url']; ?>" alt="<?php echo $authorCardPicture['alt']; ?>" />
				<?php
			} else {
				?>
				<img class="imag-no-border" src="<?php echo get_stylesheet_directory_uri() . '/images/new-design/author-default.png'; ?>" alt="Author default picture" />
				<?php
			}
			?>
		</picture>
		<div class="author-data">
			<h3 class="author-name">
				<?php
				if ( $authorCardName && $authorCardName != '' ) {
					echo $authorCardName;
				}
				?>
			</h3>
			<div class="author-position">
				<?php
				if ( $authorCardRole && $authorCardRole != '' ) {
					echo $authorCardRole;
				}
				?>
			</div>
			<div class="author-bio">
				<?php
				if ( $authorCardDescription && $authorCardDescription != '' ) {
					echo $authorCardDescription;
				}
				?>
			</div>
		</div>
	</div>
	<?php

	return true;
}

/**
 * Insert the given content in the middle of some HTML string.
 *
 * @param string $html The original HTML string.
 * @param string $insert The string to be inserted
 *
 * @return string The modified HTML string.
 *
 * Locate the middle <p> tag and insert the string after it.
 */
function insertMiddleHtmlPs( $html, $insert ) {
	// count the number of <p> tags
	$p_count = substr_count( $html, '</p>' );
	// locate the position of the middle </p>

	if ( $p_count < 1 ) {
		return $html . $insert;
	}

	$mid = floor( $p_count / 2 );
	$appearance = 0;
	$pos = 0;
	while ( $appearance < $mid ) {
		$pos = strpos( $html, '</p>', $pos ) + 3;
		$appearance++;
	}
	return substr( $html, 0, $pos + 1 ) . $insert . substr( $html, $pos + 1 );
}

/**
 * Print related content sections based on module access.
 *
 * @param int   $postid The ID of the post.
 * @param array $modulesAccess The modules to be printed.
 *
 * @return bool True after printing the related content sections.
 */
function PrintRelatedContent( $postid, $modulesAccess ) {
	if ( in_array( 'related-products', $modulesAccess, true ) ) {
		PrintRelatedProducts( $postid );
	}

	if ( in_array( 'related', $modulesAccess, true ) && ! is_related_content_inline( $postid ) ) {
		print_related_posts( $postid, 'default' );
	}

	if ( in_array( 'related-free', $modulesAccess, true ) ) {
		PrintRelatedFreePosts( $postid );
	}

	return true;
}

/**
 * Print related posts
 *
 * @param int    $postid   The ID of the post.
 * @param string $placement The placement type: 'inline' or 'default'.
 * @return bool True after printing the related posts.
 */
function print_related_posts( $postid, $placement = 'default' ) {
	$valid_placements = array( 'default', 'inline' );
	$placement        = in_array( $placement, $valid_placements, true ) ? $placement : 'default';

	$section_title = get_field( 'settings_related_post_title', 'option' );
	$num_posts     = get_field( 'settings_related_post_num', 'option' );

	if ( empty( $num_posts ) ) {
		$num_posts = 3;
	}

	$posts = get_field( 'related_contents', $postid );

	if ( $posts ) {
		$card_style    = ( 'inline' === $placement ) ? 'compact' : 'readmore';
		$section_class = 'new-related-contents';

		if ( 'inline' === $placement ) {
			$section_class .= ' inline-related';
		}
		?>
		<section class="<?php echo esc_attr( $section_class ); ?>">
			<div class="related-contents">
				<?php if ( ! empty( $section_title ) ) : ?>
					<h2><?php echo esc_html( $section_title ); ?></h2>
				<?php endif; ?>
				<div class="related-row">
					<?php
					echo '<ul class="tm-layout-grid">';

					$counter = 0;
					foreach ( $posts as $post ) {
						if ( $counter >= $num_posts ) {
							break;
						}

						if ( is_numeric( $post ) ) {
							$post = get_post( (int) $post );
						}

						if ( ! $post instanceof \WP_Post ) {
							continue;
						}
						\tamarind_base\print_post_card( $post, 'grid', $card_style );

						$counter++;
					}

					echo '</ul>';
					?>
				</div>
			</div>
		</section>
		<?php
		wp_reset_postdata();
	}

	return true;
}

/**
 * Determine if related content should be displayed inline based on content type settings.
 *
 * @param int $post_id The ID of the post to check. If not provided, uses the current post.
 *
 * @return bool True if related content should be displayed inline, false otherwise.
 */
function is_related_content_inline( $post_id ): bool {
	static $cache = array();

	if ( ! is_single() ) {
		return false;
	}

	if ( empty( $post_id ) ) {
		global $post;
		$post_id = $post->ID ?? 0;
	}

	if ( ! $post_id ) {
		return false;
	}

	// Check cache first.
	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}

	// Get post terms.
	$post_terms = wp_get_post_terms( $post_id, 'content_types', array( 'fields' => 'ids' ) );

	if ( is_wp_error( $post_terms ) || empty( $post_terms ) ) {
		$cache[ $post_id ] = false;
		return false;
	}

	// Load configuration from cache.
	$config_cache_key = 'related_content_inline_config';
	$config           = get_transient( $config_cache_key );

	if ( false === $config ) {
		$included_terms = get_field( 'related_post_content_type_inline', 'option', false );
		$excluded_terms = get_field( 'related_post_content_type_inline_excluded', 'option', false );

		$config = array(
			'included' => is_array( $included_terms ) ? array_map( 'intval', $included_terms ) : array(),
			'excluded' => is_array( $excluded_terms ) ? array_map( 'intval', $excluded_terms ) : array(),
		);

		// Cache the configuration for 12 hours.
		set_transient( $config_cache_key, $config, 12 * HOUR_IN_SECONDS );
	}

	// If no inclusions are set, return false.
	if ( empty( $config['included'] ) ) {
		$cache[ $post_id ] = false;
		return false;
	}

	// Check exclusions first (they take priority).
	if ( ! empty( array_intersect( $post_terms, $config['excluded'] ) ) ) {
		$cache[ $post_id ] = false;
		return false;
	}

	// Check inclusions.
	$result            = ! empty( array_intersect( $post_terms, $config['included'] ) );
	$cache[ $post_id ] = $result;

	return $result;
}

/**
 * Clear cache when ACF options are updated.
 */
function clear_related_content_inline_cache() {
	delete_transient( 'related_content_inline_config' );
}
add_action( 'acf/save_post', __NAMESPACE__ . '\clear_related_content_inline_cache', 20 );


/**
 * Add related posts inline to the content when applicable
 *
 * @param string $content The post content.
 * @return string Modified post content
 */
function add_related_posts_inline_to_content( $content ) {
	if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	global $post;
	if ( ! isset( $post->ID ) ) {
		return $content;
	}

	// Check if related content should be inline.
	if ( ! is_related_content_inline( $post->ID ) ) {
		return $content;
	}

	ob_start();
	print_related_posts( $post->ID, 'inline' );
	$related_posts = ob_get_clean();

	if ( empty( $related_posts ) ) {
		return $content;
	}

	// Insert related posts in the middle of the content.
	$content = insertMiddleHtmlPs( $content, $related_posts );

	return $content;
}
add_filter( 'the_content', __NAMESPACE__ . '\add_related_posts_inline_to_content', 20 );


/**
 * Print the related products section.
 *
 * @param int $postid The ID of the post.
 */
function PrintRelatedProducts( $postid ) {
	$related_picture_size = 'medium';
	$sectionTitle = get_field( 'settings_related_product_title', 'option' );
	$numPosts = get_field( 'settings_related_product_num', 'option' );

	$postsProducts = get_field( 'related_products', $postid );
	if ( $postsProducts ) {
		?>
		<section class="new-related-contents">
			<div class="related-contents">
				<h2><?php echo esc_html( $sectionTitle ); ?></h2>
				<div class="related-row">
					<?php
					foreach ( $postsProducts as $postId ) {
						if ( $numPosts == 0 ) {
							break;
						}

						if ( is_object( $postId ) ) {
							$postId = $postId->ID;
						}

						$imagen = wp_get_attachment_image_src( get_post_thumbnail_id( $postId ), $related_picture_size );
						?>
						<div class="related-post-block">
							<a href="<?php echo get_permalink( $postId ); ?>">
								<figure style="margin:0" class="background-related lazy <?php echo $related_picture_size; ?>" data-src="<?php echo $imagen[0]; ?>"></figure>
							</a>
							<div class="related-box">
								<a class="title-link" href="<?php echo get_permalink( $postId ); ?>"><?php echo get_the_title( $postId ); ?></a>
								<a class="arrow-link" href="<?php echo get_permalink( $postId ); ?>">
									<img class="icon-slider-nav" src="<?php echo get_stylesheet_directory_uri() . '/images/new-design/nav-right-white.svg'; ?>" alt="">
								</a>
							</div>
						</div>
						<?php
						$numPosts--;
					}
					?>
				</div>
			</div>
		</section>
		<?php wp_reset_postdata(); ?>
		<?php
	}

	return true;
}

function PrintRelatedFreePosts( $postid ) {
	global $post;

	$related_picture_size = 'medium';
	$sectionTitle = get_field( 'settings_related_free_post_title', 'option' );
	$numPosts = get_field( 'settings_related_free_post_num', 'option' );

	$args = array(
		'posts_per_page' => $numPosts,
		'post_type'      => 'post',
		'meta_key'       => 'open',
		'meta_value'     => '1',
	);
	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() ) {
		?>
		<section class="new-related-contents">
			<div class="related-contents">
				<h2><?php echo $sectionTitle; ?></h2>
				<div class="related-row">
					<?php
					while ( $the_query->have_posts() ) {
						$the_query->the_post();

						$imagen = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $related_picture_size );
						?>
						<div class="related-post-block">
							<a href="<?php echo get_permalink( $post->ID ); ?>">
								<figure style="margin:0" class="background-related lazy <?php echo $related_picture_size; ?>" data-src="<?php echo $imagen[0]; ?>"></figure>
							</a>
							<div class="related-box">
								<a class="title-link" href="<?php echo get_permalink( $post->ID ); ?>"><?php echo get_the_title( $post->ID ); ?></a>
								<a class="arrow-link" href="<?php echo get_permalink( $post->ID ); ?>">
									<img class="icon-slider-nav" src="<?php echo get_stylesheet_directory_uri() . '/images/new-design/nav-right-white.svg'; ?>" alt="">
								</a>
							</div>
						</div>	
						<?php
					}
					?>
				</div>
			</div>
		</section>
		<?php wp_reset_postdata(); ?>
		<?php
	}

	return true;
}

// Chat
function PrintChat() {
	if ( is_user_logged_in() ) {
		return;
	}

	$chat = get_field( 'w_chat_active', 'option' );
	if ( $chat ) {
		?>
		<div class="chat-icon">
			<a href="#chat-modal" class="tm-modal-trigger">
				<img src="<?php echo get_stylesheet_directory_uri() . '/images/new-design/chat.svg'; ?>" alt="">
			</a>
		</div>

		<dialog id="chat-modal" class="tm-modal">
			<div class="tm-modal-content">
				<button class="tm-modal-close" type="button">×</button>
				<?php
				if ( is_plugin_active( 'tamarind-forms/tamarind-forms.php' ) ) {
					\tamarind_forms\display_form\display_form( 'chat', true, get_the_id() );
				}
				?>
			</div>
		</dialog>
		<?php
	}
}

// Sidebar
function getWidgetsVisibility( $visibility ) {
	$term_ids = get_post_terms_content_type( get_the_ID(), 'term_id' );

	// solo se pondrá a true cuando encuentre un caso particular por content type, si es false se muestra el default de la configuración
	$condicionSidebarVisibility = false;

	if ( have_rows( 'single_sidebar_settings', 'options' ) ) {
		while ( have_rows( 'single_sidebar_settings', 'options' ) ) {
			the_row();

			$sidebarWidgetDefault = get_sub_field( 'sidebar_setting_default' );
			$sidebarWidgetVisibility = get_sub_field( 'select_setting_visibility' );

			if ( in_array( $visibility, $sidebarWidgetVisibility ) ) {
				if ( $sidebarWidgetDefault ) {
					$sidebarVisibilityDefault = get_sub_field( 'select_setting_widget' );
				} else {
					if ( ! $condicionSidebarVisibility ) {
						$sidebarContentTypes = get_sub_field( 'sidebar_setting_content_type' );
						$sidebarWidget = get_sub_field( 'select_setting_widget' );

						foreach ( $sidebarContentTypes as $contentType ) {
							if ( in_array( $contentType->term_id, $term_ids ) ) {
								$condicionSidebarVisibility = true;
								return $sidebarWidget;
							}
						}
					}
				}
			}
		}
	}

	if ( ! $condicionSidebarVisibility ) {
		return $sidebarVisibilityDefault;
	}

	return false;
}

function getTocVisibility() {
	$term_id = getTermId();

	$result = false;
	reset_rows();

	if ( have_rows( 'single_sidebar_settings', 'options' ) ) {
		while ( have_rows( 'single_sidebar_settings', 'options' ) ) {
			the_row();

			$sidebarWidgetDefault = get_sub_field( 'sidebar_setting_default' );
			$sidebarContentTypes = get_sub_field( 'sidebar_setting_content_type' );
			$sidebarToc = get_sub_field( 'sidebar_setting_toc' );

			if ( ! $sidebarWidgetDefault && $sidebarToc ) {
				foreach ( $sidebarContentTypes as $contentType ) {
					if ( $contentType->term_id == $term_id ) {
						if ( $sidebarToc ) {
							$result = true;
						}
					}
				}
			}
		}
	}
	return $result;
}

function printSidebar() {
	$puedoLeer = current_user_can_read_post();

	$visibility = getVisibility();
	$visibilityWidgets = getWidgetsVisibility( $visibility );

	if ( ! $puedoLeer ) {
		// TODO toc: solo muestra el TOC para los usuarios que no pueden leer porque aun esta el plugin de TOC dentro del contenido para los usuarios que pueden leer
		if ( getTocVisibility() ) {
			\tamarind_toc\printWidgetTOC();
		}
	}

	if ( in_array( 'latest', $visibilityWidgets ) ) {
		print_widget_latest_contents();
	}
	if ( in_array( 'related', $visibilityWidgets ) ) {
		print_widget_related_contents();
	}
	if ( in_array( 'alerts', $visibilityWidgets ) ) {
		print_widget_latest_alerts();
	}
	if ( in_array( 'store', $visibilityWidgets ) ) {
		print_widget_banner_store();
	}
	if ( in_array( 'benefits', $visibilityWidgets ) ) {
		print_widget_benefits();
	}

	return true;
}

function print_widget_latest_contents() {
	$widgetLatestContentsTitle = get_field( 'w_latest_contents_title', 'option' );
	$widgetLatestContentsNumber = 5;
	if ( get_field( 'w_latest_contents_num', 'option' ) != '' ) {
		$widgetLatestContentsNumber = get_field( 'w_latest_contents_num', 'option' );
	}

	$args = array(
		'post_type'              => 'post',
		'posts_per_page'         => $widgetLatestContentsNumber,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'post_status'            => 'publish',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);

	$widgetLatestContents = new WP_Query( $args );
	?>
	<section class="widget new-widget widget-latest-content">
		<div class="widget-wrap">
			<h3 class="widget-title"><?php echo $widgetLatestContentsTitle; ?></h3>
			<ul class="widget-content-list">
				<?php
				if ( $widgetLatestContents->have_posts() ) {
					while ( $widgetLatestContents->have_posts() ) {
						$widgetLatestContents->the_post();
						?>
						<li class="widget-content-list-item">
							<a href="<?php the_permalink(); ?>" class="widget-latest-content-link">
								<?php the_title(); ?>
							</a>
						</li>
						<?php
					}
				}
				?>
				<?php wp_reset_postdata(); ?>
			</ul>
		</div>
	</section>
	<?php
	return true;
}

function print_widget_related_contents() {
	$postid = get_the_ID();

	$sectionTitle = get_field( 'w_related_contents_title', 'option' );
	$numPosts = get_field( 'w_related_contents_num', 'option' );

	$posts = get_field( 'related_contents', $postid );
	if ( $posts ) {
		?>
		<section class="widget new-widget widget-related-content">
			<div class="widget-wrap">
				<h3 class="widget-title"><?php echo $sectionTitle; ?></h3>
				<ul class="widget-content-list">
					<?php
					foreach ( $posts as $postId ) {
						?>
						<?php
						if ( $numPosts == 0 ) {
							break;
						}

						if ( is_object( $postId ) ) {
							$postId = $postId->ID;
						}
						?>
						<li class="widget-content-list-item">
							<a class="widget-latest-content-link" href="<?php echo get_permalink( $postId ); ?>"><?php echo get_the_title( $postId ); ?></a>
						</li>
						<?php
						$numPosts--;
					}
					?>
				</ul>
			</div>
		</section>
		<?php wp_reset_postdata(); ?>
		<?php
	}

	return true;
}

/**
 * Get geography term IDs associated with the post that are countries.
 *
 * @param int $postid The ID of the post.
 * @return array|false Array of geography term IDs that are countries, or false on failure.
 */
function get_only_countries_geo_ids( $postid ): array|false {
	$country_ids = array();

	$geographies = get_the_terms( $postid, 'geography' );
	if ( ! $geographies || is_wp_error( $geographies ) ) {
		return false;
	}

	foreach ( $geographies as $term ) {
		// Europe, Asia, etc are parent terms, countries are child terms.
		if ( empty( $term->parent ) ) {
			continue;
		}

		$parent = get_term( (int) $term->parent, 'geography' );
		if ( ! $parent || is_wp_error( $parent ) ) {
			continue;
		}

		if ( empty( $parent->parent ) ) {
			$country_ids[] = (int) $term->term_id;
		} else {
			// States or other sub-regions are not considered countries, add country.
			$country_ids[] = (int) $parent->term_id;
		}
	}

	return array_values( array_unique( array_filter( $country_ids ) ) );
}

/**
 * Print the Latest Alerts widget if the user's plan includes alerts.
 *
 * @return bool True if the widget was printed, false otherwise.
 */
function print_widget_latest_alerts(): bool {
	if ( ! is_alerts_plan() ) {
		return false;
	}

	$widget_latest_alerts_title = get_field( 'w_latest_alerts_title', 'option' );
	$widget_latest_alerts_number = 5;
	if ( get_field( 'w_latest_alerts_num', 'option' ) !== '' ) {
		$widget_latest_alerts_number = get_field( 'w_latest_alerts_num', 'option' );
	}

	$geo_ids = get_only_countries_geo_ids( get_the_ID() );
	if ( ! $geo_ids ) {
		return false;
	}

	$get_data_plan       = get_data_plan();
	$plan_geo_alerts_tax = $get_data_plan['plan_geo_alerts_tax'] ?? null;
	if ( ! empty( $plan_geo_alerts_tax ) ) {
		// If Plan has geographical restrictions, filter geo IDs accordingly.
		$geo_ids = array_intersect( $geo_ids, $plan_geo_alerts_tax );
	}

	$args = array(
		'post_type'              => 'regulatory_alert',
		'posts_per_page'         => $widget_latest_alerts_number,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'post_status'            => 'publish',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);

	if ( ! empty( $geo_ids ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'geography',
				'field'    => 'id',
				'terms'    => $geo_ids,
			),
		);
	}

	$widget_latest_alerts_ = new WP_Query( $args );
	?>

	<section class="latest-content-sidebar tm-sidebar-module<?php echo esc_attr( $container_style ?? '' ); ?>">
		<h2 class="tm-sidebar-module__title"><?php echo esc_html( $widget_latest_alerts_title ); ?></h2>
		<?php print_post_list( $widget_latest_alerts_, 'alert' ); ?>
	</section>

	<?php
	return true;
}

function print_widget_benefits() {
	$widgetBenefitsTitle = get_field( 'w_benefits_title', 'option' );
	$widgetBenefitsText = get_field( 'w_benefits_text', 'option' );

	?>
	<section class="widget new-widget widget-benefits-content">
		<div class="widget-wrap">
			<h3 class="widget-title"><?php echo $widgetBenefitsTitle; ?></h3>
			<div class="widget-content">
				<?php echo $widgetBenefitsText; ?>
			</div>
		</div>
	</section>
	<?php
	return true;
}

function print_widget_banner_store() {
	$post_id = get_the_ID();

	$widgetBannerTitle = get_field( 'w_banner_title', 'option' );
	$widgetBannerSubtitle = get_field( 'w_banner_subtitle', 'option' );
	$widgetBannerLink = get_field( 'w_banner_link', 'option' );
	$widgetBannerBackground = get_field( 'w_banner_background', 'option' );

	$linkButtonBannerStore = $widgetBannerLink['url'];
	$txtButtonBannerStore = $widgetBannerLink['title'];

	$widgetBannerLinkToProduct = get_field( 'w_banner_link_to_product', 'option' );
	$widgetBannerLinkToProductText = get_field( 'w_banner_link_to_product_text', 'option' );

	$purchaseVersionInPost = get_field( 'purchase_version', $post_id );
	if ( $purchaseVersionInPost && $widgetBannerLinkToProduct ) {
		$purchaseVersionProduct = get_field( 'tracker_db_purchase_version', $post_id );
		if ( $purchaseVersionProduct != '' ) {
			$linkButtonBannerStore = get_permalink( $purchaseVersionProduct );
			$txtButtonBannerStore = $widgetBannerLinkToProductText;
		}
	}

	$widgetBannerBackgroundStyle = '';
	if ( $widgetBannerBackground != '' ) {
		$widgetBannerBackgroundStyle = 'background-image: url(' . $widgetBannerBackground['url'] . ');';
	}
	?>
	<section class="widget new-widget widget-banner-content" style="<?php echo $widgetBannerBackgroundStyle; ?>">
		<div class="widget-wrap">
			<h3 class="widget-title"><?php echo $widgetBannerTitle; ?></h3>
			<h4 class="widget-subtitle"><?php echo $widgetBannerSubtitle; ?></h4>
			<div class="widget-link">
				<a href="<?php echo $linkButtonBannerStore; ?>" class="widget-banner-link" target="<?php echo $widgetBannerLink['target']; ?>">
					<?php echo $txtButtonBannerStore; ?>
					<img class="icon-arrow" src="<?php echo get_stylesheet_directory_uri(); ?>/images/new-design/icon-arrow-right-white.svg" alt="">
				</a>
			</div>
		</div>
	</section>
	<?php
	return true;
}

function printSeparator() {
	?>
	<div class="new-separator"></div>
	<?php
	return true;
}

function replace_code_script( $script, $num ) {
	$script = str_replace( '<script src="https://www.billtrack50.com/js/bt50.widget.map.min.js" type="text/javascript"></script>', '', $script );
	$script = str_replace( 'BT50MapWidget', 'map-widget-' . $num, $script );
	$script = str_replace( 'BT50.Map({', 'BT50.Map({elementID: "#map-widget-' . $num . '" ,', $script );

	$script = str_replace( '<script src="https://www.billtrack50.com/js/bt50.widget.bill.min.js" type="text/javascript"></script>', '', $script );
	$script = str_replace( 'BT50Widget', 'widget-' . $num, $script );
	$script = str_replace( 'BT50.Widget({', 'BT50.Widget({elementID: "#widget-' . $num . '" ,', $script );

	return $script;
}

function do_js_code_switch_maps( $data ) {
	if ( ! is_array( $data ) ) {
		return false;
	}

	$result = "\t" . 'switch (index) {' . "\n";
	foreach ( $data as $key => $value ) {
		if ( $value == '' ) {
			continue;
		}
		$result .= "\t\t" . 'case ' . $key . ":\n";
		$result .= "\t\t\t" . $value . "\n";
		$result .= "\t\t\t" . 'break;' . "\n";
	}
	$result .= "\t\t" . 'default: console.log("No maps");' . "\n";
	$result .= "\t" . '}';
	return $result;
}

function include_script_map() {
	?>
	<script src="https://www.billtrack50.com/js/bt50.widget.map.min.js" type="text/javascript"></script>
	<script src="https://www.billtrack50.com/js/bt50.widget.bill.min.js" type="text/javascript"></script>
	<?php
}

function printTabContent() {
	global $post;

	$id_tabs_contents = get_field( 'id_tabs_contents' );
	$tabs_billtracker_maps = get_field( 'tabs_billtracker_maps' );

	ob_start();

	if ( $tabs_billtracker_maps ) {
		include_script_map();
	}

	$random = rand( 1, 666 );
	if ( have_rows( 'tabs_contents' ) ) {
		?>

		<tm-tabs id="<?php echo 'tamarind-tab-' . $id_tabs_contents; ?>" class="single-content-tabs" role="tablist">

			<nav class="tabs-header single-tabs-nav">

				<ul class="single-tabs-nav-list">
					<?php
					$contador = 1;
					while ( have_rows( 'tabs_contents' ) ) :
						the_row();
						$tab_content_name = get_sub_field( 'tab_content_name' );
						?>
						<li
							id="tab-<?php echo esc_attr( $contador ); ?>"
							class="tab-title single-tabs-tab <?php echo 0 === $contador ? 'active' : ''; ?>"
							role="tab"
							aria-selected="<?php echo 0 === $contador ? 'true' : 'false'; ?>"
							aria-controls="panel-<?php echo esc_attr( $contador ); ?>"
							tabindex="<?php echo 0 === $contador ? '0' : '-1'; ?>"
							data-target="panel-<?php echo esc_attr( $contador ); ?>">
							<?php echo esc_html( $tab_content_name ); ?>
						</li>
						<?php
						$contador++;
					endwhile;
					?>
				</ul>

			</nav>
			<div class="tabs-content single-tabs-content single-tabs-content-<?php echo esc_attr( $random ); ?>" id="tabs-content-desktop-<?php echo esc_attr( $random ); ?>">

				<?php
				$contador = 1;
				$array_script_maps = array();
				$array_script_widgets = array();
				while ( have_rows( 'tabs_contents' ) ) :
					the_row();
					$tab_content_title = get_sub_field( 'tab_content_title' );
					?>
					<div
						id="panel-<?php echo esc_attr( $contador ); ?>"
						class="tab-content single-tab-content <?php echo 0 === $contador ? 'active' : ''; ?>"
						role="tabpanel"
						aria-labelledby=panel-"<?php echo esc_attr( $contador ); ?>"
						tabindex="0"
						<?php echo 0 !== $contador ? 'hidden' : ''; ?>>

								<?php
								if ( $tab_content_title ) {
									?>
									<h3 class="single-tab-content-title"><?php echo $tab_content_title; ?></h3>
									<?php
								}
								if ( have_rows( 'tab_contents' ) ) {
									?>
									<div class="single-tab-content-text">
										<?php
										$no_contents_script = true;
										while ( have_rows( 'tab_contents' ) ) {
											the_row();
											$layout = get_row_layout();
											$content = '';
											$bt50_map_code = '';
											$bt50_widget_code = '';

											switch ( $layout ) {
												case 'text':
													$content = get_sub_field( 'content_text' );
													break;
												case 'script':
													$script = get_sub_field( 'content_script' );
													$content = replace_code_script( $script, $contador );
													if ( preg_match( '/BT50\.Map\(\{.*?\}\);/s', $content, $matches ) ) {
														$bt50_map_code = $matches[0];
														$array_script_maps[ $contador ] = $bt50_map_code;
													}
													if ( preg_match( '/BT50\.Widget\(\{.*?\}\);/s', $content, $matches ) ) {
														$bt50_widget_code = $matches[0];
														$array_script_widgets[ $contador ] = $bt50_widget_code;
													}
													$no_contents_script = false;
													break;
												default:
													// code...
													break;
											}
											echo $content;
										}
										if ( $no_contents_script ) {
											$array_script_maps[ $contador ] = '';
											$array_script_widgets[ $contador ] = '';
										}
										?>
									</div>
									<?php
								}
								?>


					</div>
					<?php
					$contador++;
				endwhile;
				?>

			</div>
		</tm-tabs>
		<?php
	}
	?>

<script type="text/javascript">
	function cambiarMapa(index) {
	<?php echo do_js_code_switch_maps( $array_script_maps ); ?>
	}

	document.addEventListener("DOMContentLoaded", function() {
	<?php
		$contador = $contador - 1;
	while ( $contador > 0 ) {
		echo "\n\t\t" . 'var boton' . $contador . ' = document.getElementById("tab-' . $contador . '");' . "\n";
		echo "\t\t" . 'boton' . $contador . '.addEventListener("click", function() {' . "\n";
		echo "\t\t\t" . 'cambiarMapa(' . $contador . ');' . "\n";
		echo "\t\t" . '});' . "\n";
		$contador = $contador - 1;
	}
	?>
	});
</script>
	<?php

	$result = ob_get_contents();
	ob_end_clean();

	return $result;
}
add_shortcode( 'tamarind-tabs', __NAMESPACE__ . '\printTabContent' );


/**
 * Enqueue rating system script for single posts.
 */
function enqueue_rating_system_script() {
	if ( ! is_single() || ! is_user_logged_in() ) {
		return;
	}

	$current_user = wp_get_current_user();
	$post_id      = get_the_ID();

	// Prepare enhanced user data.
	$enhanced_user_data = array(
		'is_logged_in'          => true,
		'id'                    => $current_user->ID,
		'name'                  => $current_user->display_name,
		'email'                 => $current_user->user_email,
		'post_id'               => $post_id,
		'nonce'                 => wp_create_nonce( 'rating_nonce' ),
		'ajax_url'              => admin_url( 'admin-ajax.php' ),
		'success_message'       => __( 'Thank you for your rating!', TM_LANGUAGE_DOMAIN ),
		'error_message'         => __( 'Error saving your rating. Please try again.', TM_LANGUAGE_DOMAIN ),
		'already_voted_message' => __( 'You have already rated this content. Thank you for your feedback.', TM_LANGUAGE_DOMAIN ),
		'saving_message'        => __( 'Saving your rating...', TM_LANGUAGE_DOMAIN ),
	);

	$plugin_url = plugin_dir_url( dirname( __DIR__, 2 ) );

	wp_register_script(
		'tm-base-rating',
		$plugin_url . 'src/js/rating-post.js',
		array(),
		'2.0.0',
		true
	);

	wp_localize_script( 'tm-base-rating', 'ratingUserData', $enhanced_user_data );

	wp_enqueue_script( 'tm-base-rating' );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_rating_system_script', 20 );


/**
 * Display the rating form container
 *
 * @return void
 */
function display_rating_form() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>

	<div class="simple-rating-container">
		<span class="rating-label">
			<?php esc_html_e( 'Rate:', 'tamarind-base' ); ?>
		</span>

		<div class="rating-stars" role="radiogroup" aria-label="<?php esc_attr_e( 'Rate this content from 1 to 5 stars', 'tamarind-base' ); ?>">
			<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
				<label class="rating-star-label" for="rating-star-<?php echo esc_attr( intval( $i ) ); ?>">
					<input type="radio" 
						id="rating-star-<?php echo esc_attr( intval( $i ) ); ?>" 
						name="post-rating" 
						value="<?php echo esc_attr( intval( $i ) ); ?>" 
						class="rating-star-input"
						aria-label="<?php printf( esc_attr__( '%d stars', TM_LANGUAGE_DOMAIN ), intval( $i ) ); ?>">
					<span class="rating-star-visual">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" aria-hidden="true" focusable="false">
							<path fill="currentColor" d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.967-7.417 3.967 1.481-8.279-6.064-5.828 8.332-1.151z"/>
						</svg>
						<span class="screen-reader-text"><?php printf( esc_html__( '%d stars', TM_LANGUAGE_DOMAIN ), intval( $i ) ); ?></span>
					</span>
				</label>
			<?php endfor; ?>
		</div>

		<div class="loading-indicator"></div>
		<div class="rating-message box-info" role="alert" aria-live="polite"></div>
	</div>
	<?php
}
add_action( 'tm_rating_post', __NAMESPACE__ . '\display_rating_form' );


/**
 * Enhanced function to save ratings to ACF fields and send to Zoho
 * This function should be called from the AJAX handler
 *
 * @param int    $post_id    The ID of the post being rated.
 * @param int    $user_id    The ID of the user submitting the rating.
 * @param int    $rating     The rating value (1-5).
 * @param string $user_name  The name of the user submitting the rating.
 * @param string $user_email The email of the user submitting the rating.
 * @return bool True on success, false on failure
 */
function save_rating_to_acf_and_zoho( $post_id, $user_id, $rating, $user_name, $user_email ) {
	// Get post data.
	$post = get_post( $post_id );
	if ( ! $post ) {
		return false;
	}

	$current_time = current_time( 'mysql' );

	// ===== SAVE TO POST ACF FIELD =====
	$post_rating_data = array(
		'date'     => $current_time,
		'username' => $user_name,
		'email'    => $user_email,
		'rating'   => $rating,
		'user_id'  => $user_id,
	);

	$existing_post_ratings = get_field( 'post_ratings', $post_id );
	if ( ! is_array( $existing_post_ratings ) ) {
		$existing_post_ratings = array();
	}
	$existing_post_ratings[] = $post_rating_data;
	update_field( 'post_ratings', $existing_post_ratings, $post_id );

	// ===== SAVE TO USER ACF FIELD =====
	$user_rating_data = array(
		'date'       => $current_time,
		'post_id'    => $post_id,
		'post_title' => $post->post_title,
		'rating'     => $rating,
		'post_url'   => get_permalink( $post_id ),
	);

	$existing_user_ratings = get_field( 'user_ratings', 'user_' . $user_id );
	if ( ! is_array( $existing_user_ratings ) ) {
		$existing_user_ratings = array();
	}
	$existing_user_ratings[] = $user_rating_data;
	update_field( 'user_ratings', $existing_user_ratings, 'user_' . $user_id );

	// ===== SEND TO ZOHO CRM =====
	// send_rating_to_zoho( $post_id, $user_id, $rating, $user_name, $user_email, $post );

	return true;
}

/**
 * Send rating data to Zoho CRM
 *
 * @param int     $post_id    The ID of the post being rated.
 * @param int     $user_id    The ID of the user submitting the rating.
 * @param int     $rating     The rating value (1-5).
 * @param string  $user_name  The name of the user submitting the rating.
 * @param string  $user_email The email of the user submitting the rating.
 * @param WP_Post $post       The post object being rated.
 * @return bool True if sent successfully, false on failure
 */
function send_rating_to_zoho( $post_id, $user_id, $rating, $user_name, $user_email, $post ) {
	// Check if Zoho function exists
	if ( ! function_exists( 'tamarind_base\send_payload_to_zoho' ) ) {
		error_log( 'Zoho function not available.' );
		return false;
	}

	// Get user first name and last name from user meta
	$first_name = get_user_meta( $user_id, 'first_name', true );
	$last_name = get_user_meta( $user_id, 'last_name', true );

	// If first_name is empty, use the display name
	if ( empty( $first_name ) ) {
		$first_name = $user_name;
	}

	// Get platform value from ACF options page
	$zoho_platform = get_field( 'platform', 'option' );

	// Prepare Zoho payload based on the example format
	$zoho_payload = array(
		'lead_owner'       => 'Erik Galavis',
		'lead_status'      => 'New',
		'first_name'       => $first_name,
		'last_name'        => $last_name,
		'corporate_email'  => $user_email,
		'rate'             => $rating,
		'zoho_input_name'  => $post->post_title,
		'zoho_platform'    => $zoho_platform,
		'zoho_lead_action' => 'Rate this page',
	);

	// Remove empty values (specifically for last_name which might be empty)
	$zoho_payload = array_filter( $zoho_payload );

	try {
		// Send to Zoho
		$zoho_config = array(
			'payload'  => json_encode( $zoho_payload ),
			'function' => 'cc_triage_endpoint',
		);

		$result = \tamarind_base\send_payload_to_zoho( $zoho_config );

		if ( $result ) {
			error_log( 'Rating successfully sent to Zoho for post ID: ' . $post_id );
		} else {
			error_log( 'Failed to send rating to Zoho for post ID: ' . $post_id );
		}

		return true;
	} catch ( Exception $e ) {
		error_log( 'Zoho API error: ' . $e->getMessage() );
		return true;
	}
}

/**
 * AJAX handler for saving ratings
 */
function handle_save_rating_ajax() {
	// Verify nonce.
	if ( ! wp_verify_nonce( $_POST['nonce'], 'rating_nonce' ) ) {
		wp_send_json_error( 'Security error' );
	}

	// Validate data.
	$rating     = intval( $_POST['rating'] );
	$post_id    = intval( $_POST['post_id'] );
	$user_id    = intval( $_POST['user_id'] );
	$user_name  = sanitize_text_field( $_POST['user_name'] );
	$user_email = sanitize_email( $_POST['user_email'] );

	if ( $rating < 1 || $rating > 5 ) {
		wp_send_json_error( 'Invalid rating value' );
	}

	if ( $post_id <= 0 ) {
		wp_send_json_error( 'Invalid post ID' );
	}

	// Save to ACF and send to Zoho.
	$result = save_rating_to_acf_and_zoho( $post_id, $user_id, $rating, $user_name, $user_email );

	if ( $result ) {
		wp_send_json_success( 'Rating saved successfully' );
	} else {
		wp_send_json_error( 'Error saving rating' );
	}
}
add_action( 'wp_ajax_save_rating', __NAMESPACE__ . '\handle_save_rating_ajax' );
add_action( 'wp_ajax_nopriv_save_rating', __NAMESPACE__ . '\handle_save_rating_ajax' );
