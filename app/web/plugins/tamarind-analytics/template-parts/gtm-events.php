<?php
/**
 * Generates the Google Consent Mode V2 script tag.
 *
 * @package TamarindAnalytics
 * @var $args Arguments passed to the template.
 * @return void
 */

defined( 'ABSPATH' ) || exit;

$events = $args['events'] ?? null;
if ( empty( $events ) || ! is_array( $events ) ) {
	return;
}
?>
<script>
	window.dataLayer = window.dataLayer || [];
	<?php foreach ( $events as $event ) { ?>
		window.dataLayer.push( <?php echo wp_json_encode( $event ); ?> );
	<?php } ?>
</script>
