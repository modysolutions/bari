<?php
/**
 * Generates the Google Consent Mode V2 script tag.
 *
 * @package TamarindAnalytics
 * @var $args Arguments passed to the template.
 * @return void
 */

defined( 'ABSPATH' ) || exit;

$consent_wait_for_update = (int) $args['consent_wait_for_update'] ?? 0;
?>
<!-- GOOGLE CONSENT MODE V2 -->
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag() {
		dataLayer.push(arguments);
	}
	gtag("consent", "default", {
		ad_storage: "denied",
		ad_user_data: "denied", 
		ad_personalization: "denied",
		analytics_storage: "denied",
		functionality_storage: "denied",
		personalization_storage: "denied",
		security_storage: "granted",
		wait_for_update: <?php echo esc_js( $consent_wait_for_update ); ?>,
	});
</script>
