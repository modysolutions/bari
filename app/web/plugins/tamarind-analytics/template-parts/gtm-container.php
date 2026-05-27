<?php
/**
 * Generates the Google Tag Manager container script tag.
 *
 * @package TamarindAnalytics
 * @var $args Arguments passed to the template.
 * @return void
 */

defined( 'ABSPATH' ) || exit;

$gtm_container_id = (string) $args['gtm_container_id'] ?? '';
if ( empty( $gtm_container_id ) ) {
	return;
}
?>
<!-- GOOGLE TAG MANAGER -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','<?php echo esc_js( $gtm_container_id ); ?>');</script>
