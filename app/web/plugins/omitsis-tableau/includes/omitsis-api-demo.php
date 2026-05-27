<?php
/**
 * Handle common rest api functions
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\tableau_api_demo;

defined( 'ABSPATH' ) || exit;

function init_tableau_api () {

	echo '<div class="wrap"><h1>Demo</h1></div>';
	echo '<br><br>';

	$jwt = TABLEAU_JWT;
	$site = TABLEAU_SITE;

	echo '<b>JWT:</b> <br>';
	echo '<pre>';
	print_r($jwt);
	echo '</pre>';

	echo '<div style="width: 100%; background: #fff; max-width: 1420px;">';
	echo do_shortcode('[my-tableau project="Disposables Tracker" dashboard="Overall Brand" width="1920px" height="2048px"]');
	echo '</div>';

}
