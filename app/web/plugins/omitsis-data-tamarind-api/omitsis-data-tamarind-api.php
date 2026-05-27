<?php
/**
 * Omitsis Base Plugin
 *
 * @package           Omitsis_Data_Tamarind_Api
 * @author            Omitsis
 * @copyright         2023 Omitsis
 *
 * @wordpress-plugin
 * Plugin Name:       Omitsis Data Tamarind Api
 * Author:            Omitsis
 * Author URI:        https://omitsis.com
 * Description:       Data Tamarind Api by Omitsis.
 * Version:           1.1.1
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Text Domain:       omitsis-data-tamarind-api
 */

namespace omitsis_data_api;

/**
 * Used to ensure that the plugin file is being accessed through the WordPress environment.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Version number of the omitsis base plugin
*/
const VERSION = '1.1.1';

/**
 * The unique identifier of this plugin.
 */
const PLUGIN_NAME = 'omitsis-data-tamarind-api';

/* The line `const PLUGIN_PATH = __DIR__;` is defining a constant variable named `PLUGIN_PATH` and
assigning it the value of the current directory path of the plugin file. The `__DIR__` constant
represents the directory path of the current file. So, `PLUGIN_PATH` will contain the absolute path
of the plugin directory. */
const PLUGIN_PATH = __DIR__;

require_once PLUGIN_PATH . '/includes/omitsis-plugin-actions.php';
require_once PLUGIN_PATH . '/admin/omitsis-admin.php';
require_once PLUGIN_PATH . '/includes/omitsis-acfs.php';
require_once PLUGIN_PATH . '/includes/omitsis-rest-api.php';

use omitsis\plugin_actions as plugin_actions;
use omitsis\admin as admin;
use omitsis\acfs as acfs;
use omitsis\rest_api as rest_api;

/* This file likely contains functions or definitions related to internationalization and localization. */
require_once PLUGIN_PATH . '/i18n/omitsis-languages.php';
use omitsis\i18n as i18n;

/* __FILE__ constant as an argument. This function is responsible for initializing the actions and hooks for the plugin. */
plugin_actions\init_actions( __FILE__ );

/* This function is responsible for initializing the actions and hooks related to the admin area of the plugin. */
admin\init_admin();

/* This function is responsible for initializing the actions and hooks related to the Advanced Custom Fields (ACF) plugin. */
acfs\init_acfs();

/* Initializing the actions and hooks related to internationalization and localization in the plugin. */
i18n\init_i18n();

/* This function is responsible for initializing the actions and hooks related to the REST API in the plugin. */
rest_api\init_rest_api();
