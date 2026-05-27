<?php
/**
 * Podcast item template
 *
 * A single item in a podcast archive listing
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use function tamarind_subscriptions\access\{current_user_can_read_post};

$pt = get_post_type();
if ( $pt == 'regulatory_alert' ) {
	$title = alertTitleMaxLength( $post->post_title );
} else {
	$title = $post->post_title;
}

if ( current_user_can_read_post() ) {
	$puedoLeer = true;
	$locked = '<img class="icon-candado" src="' . get_stylesheet_directory_uri() . '/images/new-design/search-user-access-icon.svg" alt="">';
	$candado = "[['available', 'Content included in subscription', 'show-all']]";
} else {
	$puedoLeer = false;
	$locked = '<img class="icon-candado" src="' . get_stylesheet_directory_uri() . '/images/new-design/search-user-access-icon-locked.svg" alt="">';
	$candado = "[['not-available', 'Content not included in subscription', 'show-all']]";
}

$postid = get_the_ID();
$taxonomy = 'content_types';
$terms = get_the_terms( $postid, $taxonomy );

if ( $terms && ! is_wp_error( $terms ) ) :
	$contentTypeData_links = array();

	foreach ( $terms as $term ) {
		$style = ' style="background: #ddd; color: #343435;"';

		if ( get_field( 'background_color', $term ) ) {
			$style = ' style="background: ' . get_field( 'background_color', $term ) . '; color: ' . get_field( 'text_color', $term ) . ';"';
		}

		$contentTypeData_links[] = '<a href="' . get_term_link( $term ) . '" ' . $style . '> ' . $term->name . '</a>';
	}
	$on_contentTypeLinks = join( '', $contentTypeData_links );
	$on_contentTypeLinks = '<div class="new-search-content-type">' . $on_contentTypeLinks . '</div>';
endif;

// Forzar que no aparezcan los content_types encima de la imagen
$on_contentTypeLinks = '';
?>

<article id="post-<?php the_ID(); ?>" class="new-search-card favourite-icon-inside">
	<figure class="lazycontainer-fix">
		<?php
		if ( get_post_type() == 'regulatory_alert' ) {
			echo $on_contentTypeLinks;
			$image_alerts = get_field( 'alerts_thumb', 'options' );
			?>
			<img data-src="<?php echo $image_alerts['sizes']['medium']; ?>" class="attachment-post-thumbnail lazy" alt=""/>
			<?php
		} else {
			echo $on_contentTypeLinks;
			?>
			<img data-src="<?php the_post_thumbnail_url( 'medium' ); ?>" class="attachment-post-thumbnail lazy" alt=""/>
		<?php } ?>

		<?php
		do_action( 'tm_card_after_thumbnail', get_the_ID() );
		?>
	</figure>

	<div class="new-search-card-header">
		<div class="new-search-card-icon">
			<?php echo $locked; ?>
		</div>
		<div class="new-search-card-text">
			<h2 class="new-search-card-text-title">
				<a href="<?php the_permalink(); ?>">
					<?php
					echo esc_html( $title );
					?>
				</a>
			</h2>
			<h4 class="new-search-card-text-meta">
				<?php
				echo 'Written by ' . get_the_author() . ' | ' . get_the_date();
				?>
			</h4>
		</div>
	</div>

	<?php
	if ( get_post_type() == 'regulatory_alert' ) {
		?>
		<div class="new-search-card-header">
			<div class="new-search-card-text">
				<h2 class="new-search-card-text-title">
					<?php
					if ( $puedoLeer ) {
						echo '<div class="new-search-card-text-title-alert">';
						excerpt( '20' );
						echo '</div>';
					} else {
						// echo alertTitleMaxLength( $post->post_title );
					}
					?>
				</h2>
			</div>
		</div>
		<?php
	} else {
		excerpt( '35' );
		// echo str_replace('&nbsp;','', str_replace('[toc]','', wp_trim_words( get_the_content(), 30, '...' )));
	}
	?>

</article>
