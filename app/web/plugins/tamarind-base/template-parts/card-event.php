<?php
/**
 * Generates the HTML structure of a post Card with Event.
 *
 * @package Tamarind_Base
 * @param \WP_Post $content Post object.
 * @param string $style Custom style.
 * @return string HTML of the card.
 */

namespace tamarind_base;

defined( 'ABSPATH' ) || exit;


// Get the layout values.
$class_slider = ( 'slider' === $args['template_type_parent'] ) ? ' swiper-slide' : '';
$class_style  = ( $args['item_style'] ) ? ' tm-post-card--' . $args['item_style'] : '';
if ( ! is_a( $content, 'WP_Post' ) ) {
	return '';
}

// Get the content values.
$event_id      = $content->ID;
$content_title = $content->post_title;

// Get event data from Tamarind Events fields.
$event_name        = get_the_title();
$event_place       = get_field( 'event_place_name', $event_id );
$event_date_start  = get_field( 'event_date_start', $event_id );
$event_date_end    = get_field( 'event_date_end', $event_id );
$event_website     = get_field( 'event_website', $event_id );
$event_is_featured = get_field( 'event_featured', $event_id );
$event_logo_id     = get_field( 'event_picture', $event_id );
$event_logo        = $event_logo_id ? wp_get_attachment_image_url( $event_logo_id, 'middle' ) : '';
$button_text       = get_field( 'event_button_text', $event_id );

// Format date.
$start_date = \DateTime::createFromFormat( 'Y-m-d', $event_date_start );
$end_date   = \DateTime::createFromFormat( 'Y-m-d', $event_date_end );

$event_date_display = $start_date->format( 'jS F' );
if ( $event_date_end && $event_date_end !== $event_date_start ) {
	$event_date_display = $start_date->format( 'jS' ) . ' - ' . $end_date->format( 'jS F' );
}
?>

<li class="eventbox 
	<?php
	if ( $event_is_featured ) {
		echo ' eventbox-featured';
	}
	?>
	<?php echo esc_attr( $class_style . $class_slider ); ?>">
	<article class="eventbox__inner">
		<div class="eventbox__thumbnail">
			<?php
			if ( $event_is_featured && $event_logo ) {
				?>
				<figure class="event--logo">
					<img src="<?php echo esc_url( $event_logo ); ?>" alt="<?php echo esc_attr( $event_name ); ?>" />
				</figure>
			<?php } else { ?>
				<figure class="event--logo">
					<img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/icon-conference.svg' ); ?>" alt="default icon" />
				</figure>
			<?php } ?>
			<?php do_action( 'tm_card_after_thumbnail', $event_id ); ?>
		</div>
		<div class="eventbox__content">
			<h3 class="event--title"><?php echo esc_html( $content_title ); ?></h3>
			<div class="event--info">
				<?php if ( $event_place ) { ?>
					<i class="fa fa-map-marker"></i> <?php echo esc_html( $event_place ); ?><br>
				<?php } ?>
				<i class="fa fa-calendar"></i> <?php echo esc_html( $event_date_display ); ?>
			</div>
			<div class="event--buttons">
				<?php if ( $event_is_featured && $button_text ) { ?>
					<p>
						<button data-event="<?php echo esc_attr( $event_name ); ?>" data-tm-modal-target="#event-meetup-form" class="tm-btn btn-login tm-modal-trigger" rel="noopener">
							<?php echo esc_html( $button_text ); ?>
						</button>
					</p>
				<?php } ?>
				<?php if ( $event_website ) { ?>
					<p>
						<a href="<?php echo esc_url( $event_website ); ?>" class="tm-btn btn-register" rel="noopener" target="_blank"><?php esc_html_e( 'Event website', 'tamarind-base' ); ?></a>
					</p>
				<?php } ?>
			</div>

		</div>
	</article>
</li>
