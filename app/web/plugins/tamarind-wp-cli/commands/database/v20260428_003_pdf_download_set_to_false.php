<?php
namespace tamarind_wp_cli;

use WP_Query;

class Migration_v20260428_003_pdf_download_set_to_false {
    public function up(): void {
        $taxonomies = [
            'map',
            'database',
            'pricing',
            'market-snapshots',
            'product-tracker',
            'brands-tracker',
            'disposable-tracker',
            'flavour-nicotine-tracker',
            'hardware-tracker',
            'nicotine-tracker',
        ];

        \WP_CLI::log("🚀 Setting 'post_create_pdf' to true for posts in specified taxonomies...");
        \WP_CLI::log("📋 Taxonomies: " . implode(', ', $taxonomies));

        $args = [
            'post_type' => 'post',
            'tax_query' => [
                [
                    'relation' => 'OR',
                    [
                        'taxonomy' => 'content_types',
                        'field' => 'slug',
                        'terms' => $taxonomies,
                        'operator' => 'IN',
                    ]
                ]
            ],
            'posts_per_page' => -1,
        ];

        $query = new \WP_Query($args);

        $csv_data = [];

        if ($query->have_posts()) {
            \WP_CLI::log("✅ Found {$query->found_posts} posts. Updating 'post_create_pdf' and logging details...");
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $old_value = get_field('post_create_pdf', $post_id);
                update_post_meta($post_id, 'post_create_pdf_old_value', $old_value);
                update_field('post_create_pdf', false, $post_id);

                $csv_data[] = [
                    'ID' => $post_id,
                    'Title' => get_the_title(),
                    'Permalink' => get_permalink(),
                    'Edit Link' => get_edit_post_link($post_id, 'url'),
                ];
            }
            wp_reset_postdata();
        }

        \WP_CLI::success("✅ Updated 'post_create_pdf' for all relevant posts. Generating CSV report...");
        \WP_CLI::log("📁 CSV file will be saved in the uploads directory with the name 'set_to_true_posts.csv'.");
        $this->generate_csv($csv_data);
    }

    public function down(): void {
        $taxonomies = [
            'map',
            'database',
            'pricing',
            'market-snapshots',
            'product-tracker',
            'brands-tracker',
            'disposable-tracker',
            'flavour-nicotine-tracker',
            'hardware-tracker',
            'nicotine-tracker',
        ];

        $args = [
            'post_type' => 'post',
            'tax_query' => [
                [
                    'relation' => 'OR',
                    [
                        'taxonomy' => 'content_types',
                        'field' => 'slug',
                        'terms' => $taxonomies,
                        'operator' => 'IN',
                    ]
                ]
            ],
            'posts_per_page' => -1,
        ];

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $old_value = get_post_meta($post_id, 'post_create_pdf_old_value', true);

                update_field('post_create_pdf', $old_value, $post_id);
                delete_post_meta($post_id, 'post_create_pdf_old_value');
            }
            wp_reset_postdata();
        }
    }

    private function generate_csv(array $data): void {
        $file_path = wp_upload_dir()['basedir'].'/'.'set_to_false_posts.csv';
        $file = fopen($file_path, 'w');

        fputcsv($file, ['ID', 'Title', 'Permalink', 'Edit Link']);

        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        fclose($file);
    }
}
