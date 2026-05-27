<?php
/**
 * Generates the Universal Analytics script tag.
 *
 * @package TamarindAnalytics
 * @var $args Arguments passed to the template.
 * @return void
 */

defined( 'ABSPATH' ) || exit;

$ua = $args['ua'] ?? null;
if ( empty( $ua ) || ! is_array( $ua ) ) {
	return;
}
?>
<!-- ANALYTICS -->
<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', <?php echo wp_json_encode( $ua['tracking_id'] ?? '' ); ?>, <?php echo wp_json_encode( $ua['domain'] ?? '' ); ?>);

	<?php if ( ! empty( $ua['user_id'] ) ) { ?>
		ga('set', 'userId', <?php echo wp_json_encode( (string) $ua['user_id'] ); ?>);
	<?php } ?>

	<?php if ( ! empty( $ua['dimensions'] ) && is_array( $ua['dimensions'] ) ) { ?>
		<?php
		foreach ( $ua['dimensions'] as $dim_key => $dim_value ) {
			?>
			ga('set', <?php echo wp_json_encode( (string) $dim_key ); ?>, <?php echo wp_json_encode( (string) $dim_value ); ?>);
			<?php
		}
		?>
	<?php } ?>

	ga('send', 'pageview');
</script>
