<?php
namespace tamarind_wp_cli;

class Migration_v20260327_001_add_index_table {
    private $table_name;
    public function __construct() {
        $this->table_name = 'tamarind_search_index';
    }

    public function up() {
        global $wpdb;
        $table = $wpdb->prefix . $this->table_name;
        $sql = "CREATE TABLE $table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id bigint(20) UNSIGNED NOT NULL,
            content_buffer LONGTEXT NOT NULL,
            post_type varchar(20) NOT NULL,
            tax_topics text,
            tax_geographies text,
            tax_content_type varchar(50),
            last_updated datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY post_id (post_id),
            FULLTEXT KEY search_idx (content_buffer)
        ) {$wpdb->get_charset_collate()};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function down() {
        global $wpdb;
        $table = $wpdb->prefix . $this->table_name;
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
}
