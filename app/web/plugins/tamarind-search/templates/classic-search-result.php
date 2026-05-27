<?php
/**
 * Classic Search Result Template
 *
 * This template is used to display search results in the classic format.
 *
 * @package TamarindSearch
 */

 use function tamarind_subscriptions\access\{current_user_can_read_post};

$pt = get_post_type();
if ( 'regulatory_alert' === $pt ) {
	$title = alertTitleMaxLength($post->post_title);
} else {
	$title = $post->post_title;
}

if ( current_user_can_read_post() ) {
	$puedoLeer = true;
	$locked    = '<img class="icon-candado" src="' . get_stylesheet_directory_uri() . '/images/new-design/search-user-access-icon.svg" alt="">';
	$candado   = "[['available', 'Content included in subscription', 'show-all']]";
} else {
	$puedoLeer = false;
	$locked    = '<img class="icon-candado" src="' . get_stylesheet_directory_uri() . '/images/new-design/search-user-access-icon-locked.svg" alt="">';
	$candado   = "[['not-available', 'Content not included in subscription', 'show-all']]";
}

$postid   = get_the_ID();
$taxonomy = 'content_types';
$terms    = get_the_terms( $postid, $taxonomy );

$on_contentTypeData  = '';
$on_contentTypeLinks = '';
$on_geoData          = '';
$on_topicsData       = '';

if ( $terms && ! is_wp_error( $terms ) ) :
	$contentTypeData_links = array();
	$contentTypeData       = array();
	foreach ( $terms as $term ) {
		$style = ' style="background: #ddd; color: #343435;"';

		if ( get_field( 'background_color', $term ) ) {
			$style = ' style="background: ' . get_field( 'background_color', $term ) . '; color: ' . get_field( 'text_color', $term ) . ';"';
		}

		$contentTypeData_links[] = '<a href="' . get_term_link($term) . '" ' . $style . '> ' . $term->name . '</a>';
		$contentTypeData[]       = "['" . $term->slug . "', '" . $term->name . "', 'show-all', " . $term->term_id . ", " . $term->parent . ", 'hidden-icon']";
	}
	// $on_contentTypeLinks = join( " , ", $contentTypeData_links );
	$on_contentTypeLinks = join("", $contentTypeData_links);
	$on_contentTypeLinks = '<div class="new-search-content-type">' . $on_contentTypeLinks . '</div>';
	$on_contentTypeData  = "[" . join( ",", $contentTypeData ) . "]";
endif;

$taxonomy = 'geography';
$terms    = get_the_terms($postid, $taxonomy);

if ( $terms && ! is_wp_error( $terms ) ) :
	$geoData = array();
	foreach ( $terms as $term ) {
		// print_r($term);
		$geoData[] = "['" . $term->slug . "', '" . $term->name . "', 'show-all', " . $term->term_id . ", " . $term->parent . ", 'hidden-icon']";
	}
	$on_geoData = "[" . join( ",", $geoData ) . "]";
endif;

$taxonomy = 'topics';
$terms    = get_the_terms($postid, $taxonomy);

if ( $terms && ! is_wp_error( $terms ) ) :
	$topicsData = array();
	foreach ( $terms as $term ) {
		if ( get_field( 'topic_show_in_edit_post', 'topics_' . $term->term_id ) ) {
			$topicsData[] = "['" . $term->slug . "', '" . $term->name . "', 'show-all']";
		}
	}
	$on_topicsData = "[" . join(",", $topicsData) . "]";
endif;

$dateData = get_the_date( 'Y-m-d' ); ?>

<article data-id="id-<?php the_ID(); ?>" data-lock="<?php echo $candado; ?>" data-ctype="<?php echo $on_contentTypeData; ?>" data-geo="<?php echo $on_geoData; ?>" data-topics="<?php echo $on_topicsData; ?>" data-date="<?php echo $dateData; ?>" id="post-<?php the_ID(); ?>" class="new-search-card">

	<figure class="lazycontainer-fix favourite-icon-inside">
		<?php 
		if ( get_post_type() === 'regulatory_alert') {
			echo $on_contentTypeLinks;
			$image_alerts = get_field( 'alerts_thumb', 'options' );
			?>
			<img data-src="<?php echo $image_alerts['sizes']['medium']; ?>" class="attachment-post-thumbnail lazy" alt="" />
		<?php } else {
			echo $on_contentTypeLinks;
			?>
			<img data-src="<?php the_post_thumbnail_url( 'medium' ); ?>" class="attachment-post-thumbnail lazy" alt="" />
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
				<?php
				if ( ( ! $puedoLeer ) && ( get_post_type() === 'regulatory_alert' ) ) {
					echo esc_html( $title );
				} else {
					?>
					<a href="<?php the_permalink(); ?>">
						<?php echo esc_html( $title ); ?>
					</a>
					<?php
				}
				?>
			</h2>
			<h4 class="new-search-card-text-meta">
				<?php
				if ( get_post_type() === 'regulatory_alert' ) {
					$author = 'ECigIntelligence';
				} else {
					$author = get_the_author();
				}
				echo 'Written by ' . esc_html( $author ) . ' | ' . get_the_date();
				?>
			</h4>
		</div>
	</div>

	<?php
	if ( get_post_type() === 'regulatory_alert' ) {
		?>
		<div class="new-search-card-header">
			<div class="new-search-card-text">
				<h2 class="new-search-card-text-title">
					<?php
					if ($puedoLeer) {
						echo '<div class="new-search-card-text-title-alert">';
						excerpt('20');
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
		excerpt( '30' );
	}
	?>

	<div class="clear"></div>
</article>