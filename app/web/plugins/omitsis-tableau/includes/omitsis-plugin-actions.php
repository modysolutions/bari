<?php

/**
 * Handle common plugin actions
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\tableau_plugin_actions;

defined( 'ABSPATH' ) || exit;

/**
 * The code that runs during plugin activation.
 */
function activate_omitsis_plugin() {

}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_omitsis_plugin() {

}

/**
 * The code that runs during plugin uninstall.
 */
function uninstall_omitsis_plugin() {

}

/**
 * The function "init_actions" registers activation, deactivation, and uninstall hooks for the
 * "omitsis_data_tamarind_api" plugin.
 *
 * @param file The `` parameter is the path to the main plugin file. It is used to register
 * activation, deactivation, and uninstall hooks for the plugin.
 */
function init_actions( $file ) {
	register_activation_hook( $file, __NAMESPACE__ . '\activate_omitsis_plugin');
	register_deactivation_hook( $file, __NAMESPACE__ . '\deactivate_omitsis_plugin');
	register_uninstall_hook( $file, __NAMESPACE__ . '\uninstall_omitsis_plugin');
}
