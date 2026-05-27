<?php
/**
 * Plugin Name:     Tamarind Custom WP CLI
 * Plugin URI:      https://www.omitsis.com
 * Description:     Create new commands for wp-cli to execute with Cron: <code>wp tamarind_calculate_relevancy</code> Calculate coefficient for searches. <code>wp tamarind set-tocs</code> Generate TOC in posts with shortcode [toc]. <code>wp tamarind set-pdfs</code> Generate PDFs in posts. <code>wp tamarind reset-pdfs</code> Reset flag PDFs. <code>wp tamarind_purge_featured_posts</code> Remove as featured posts from more than a year ago. <code>wp tamarind_create_favourites_history_table</code> Create favourites history table. <code>wp tamarind_create_search_history_table</code> Create search history table.
 * Author:          Omitsis
 * Author URI:      https://www.omitsis.com
 * Text Domain:     tamarind-wp-cli
 * Domain Path:     /languages
 * Version:         2.0
 *
 * @package         Tamarind_WP_CLI
 * phpcs: disable Generic.Commenting.DocComment.ShortNotCapital
 */

namespace tamarind_wp_cli;

defined( 'ABSPATH' ) || exit;

require 'commands/indexer.php';
require 'commands/migrations.php';
require 'commands/pdf.php';
require 'commands/post.php';
require 'commands/report.php';
