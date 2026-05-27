<?php
/**
 * Generates a script tag that pushes a payload to the dataLayer.
 *
 * @package TamarindAnalytics
 * @var array $args Arguments passed to the template.
 * @return void
 */

defined( 'ABSPATH' ) || exit;

$payload = $args['payload'] ?? null;
if ( empty( $payload ) || ! is_array( $payload ) ) {
	return;
}
?>
<script>
	window.dataLayer = window.dataLayer || [];
	window.dataLayer.push( <?php echo wp_json_encode( $payload ); ?> );
</script>
