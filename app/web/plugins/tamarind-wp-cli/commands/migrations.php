<?php
namespace tamarind_wp_cli;

use WP_CLI;

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'tamarind migrate', __NAMESPACE__ . '\Migration_Handler' );
}

class Migration_Handler {
    private string $table_logs;

    public function __construct() {
        global $wpdb;
        $this->table_logs = $wpdb->prefix . 'migrations';
    }

    public function up() : void {
        $this->init_migration_table();
        $files = glob(__DIR__ . '/database/*.php');
        sort($files);

        foreach ($files as $file) {
            $version = basename($file, '.php');
            if (!$this->is_migrated($version)) {
                WP_CLI::log("🚀 Migrating: $version");
                require_once $file;

                $class = __NAMESPACE__ . '\\' . $this->get_class_name($version);
                $migration = new $class();
                $migration->up();

                $this->log_migration($version);
                WP_CLI::success("✅ Version $version applied.");
            }
        }
    }

    public function down() : void {
        global $wpdb;
        $last = $wpdb->get_row("SELECT version FROM {$this->table_logs} ORDER BY id DESC LIMIT 1");

        if ($last) {
            $version = $last->version;
            $file = __DIR__ . "/database/{$version}.php";

            if (file_exists($file)) {
                require_once $file;
                $class = __NAMESPACE__ . '\\' . $this->get_class_name($version);
                $migration = new $class();
                $migration->down();

                $wpdb->delete($this->table_logs, ['version' => $version]);
                WP_CLI::success("🔙 Reverted: $version");
            }
        } else {
            WP_CLI::error("No migrations to revert.");
        }
    }

    private function init_migration_table() : void {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$this->table_logs} (
            id int NOT NULL AUTO_INCREMENT,
            version varchar(255) NOT NULL,
            migrated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function create( $args ) : void {
        $raw_name = $args[0];
        $slug = strtolower(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $raw_name)));

        $timestamp = date('Ymd');
        $directory = __DIR__ . '/database/';

        if ( ! file_exists( $directory ) ) {
            mkdir( $directory, 0755, true );
        }

        $existing_files = glob($directory . "*.php");
        $sequence = str_pad(count($existing_files) + 1, 3, '0', STR_PAD_LEFT);

        $version = "v{$timestamp}_{$sequence}_{$slug}";
        $filename = "{$version}.php";
        $class_name = "Migration_{$version}";

        $template = "<?php\n" .
                    "namespace tamarind_wp_cli;\n\n" .
                    "class {$class_name} {\n" .
                    "    public function up() {\n" .
                    "        global \$wpdb;\n" .
                    "        // \$table = \$wpdb->prefix . 'tabla';\n" .
                    "    }\n\n" .
                    "    public function down() {\n" .
                    "        global \$wpdb;\n" .
                    "    }\n" .
                    "}\n";

        file_put_contents($directory . $filename, $template);

        WP_CLI::success("Migration created: tamarind-wp-cli/commands/database/$filename");
    }

    private function is_migrated($version) : bool {
        global $wpdb;
        return (bool) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->table_logs} WHERE version = %s", $version));
    }

    private function log_migration($version) : void {
        global $wpdb;
        $wpdb->insert($this->table_logs, ['version' => $version]);
    }

    private function get_class_name($version) : string {
        return 'Migration_' . str_replace(['.', '-'], '_', $version);
    }
}
