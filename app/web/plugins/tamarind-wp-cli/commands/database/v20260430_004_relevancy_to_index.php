<?php

namespace tamarind_wp_cli;

class Migration_v20260430_004_relevancy_to_index {
    private string $table_name;
    private \wpdb $wpdb;

    public function __construct() {
        global $wpdb;
        $this->table_name = 'tamarind_search_index';
        $this->wpdb = $wpdb;
    }

    public function up(): void {
        $table = $this->wpdb->prefix.$this->table_name;

        $sql_relevancy = "ALTER TABLE $table ADD COLUMN relevancy_score FLOAT DEFAULT 0 AFTER tax_content_type";
        $this->wpdb->query($sql_relevancy);

        $sql_published_date = "ALTER TABLE $table ADD COLUMN published_date DATETIME DEFAULT NULL";
        $this->wpdb->query($sql_published_date);
    }

    public function down(): void {
        $table = $this->wpdb->prefix.$this->table_name;

        $sql_drop_relevancy = "ALTER TABLE $table DROP COLUMN relevancy_score";
        $this->wpdb->query($sql_drop_relevancy);

        $sql_drop_published_date = "ALTER TABLE $table DROP COLUMN published_date";
        $this->wpdb->query($sql_drop_published_date);
    }
}
