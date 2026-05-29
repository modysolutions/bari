<?php

namespace App\Hooks;

class Gutenberg {

    public function init(): void {
        $this->actions();
        $this->filters();
    }

    // ─── Actions ─────────────────────────────────────────────────────────────

    private function actions(): void {
        add_action( 'after_setup_theme',       [ $this, 'setup' ] );
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_assets' ] );
        add_action( 'enqueue_block_assets',    [ $this, 'enqueue_block_assets' ] );
    }

    // ─── Filters ─────────────────────────────────────────────────────────────

    private function filters(): void {
        add_filter( 'allowed_block_types_all', [ $this, 'allowed_block_types_all' ], 10, 2 );
        add_filter( 'block_categories_all',    [ $this, 'block_categories_all' ],    10, 2 );
    }

    // ─── Theme Setup ─────────────────────────────────────────────────────────

    /**
     * Declare block-editor feature support so Gutenberg, Astra and third-party
     * blocks know what the theme is capable of.
     */
    public function setup(): void {
        // Wide / full-width block alignment support
        add_theme_support( 'align-wide' );

        // Opt-in to default block CSS (button, image, separator styles…)
        add_theme_support( 'wp-block-styles' );

        // Responsive iframes/embeds
        add_theme_support( 'responsive-embeds' );

        // Load a stylesheet into the editor that mirrors the frontend
        add_theme_support( 'editor-styles' );
        add_editor_style( 'dist/editor.css' );
    }

    // ─── Editor Assets ───────────────────────────────────────────────────────

    /**
     * Enqueue scripts and styles that load ONLY inside the block editor.
     */
    public function enqueue_editor_assets(): void {
        $asset_file = APP_THEME_DIR . '/dist/editor.asset.php';

        if ( ! file_exists( $asset_file ) ) {
            return;
        }

        $asset = require $asset_file;

        wp_enqueue_script(
            'bari-editor',
            APP_THEME_URI . '/dist/editor.js',
            $asset['dependencies'],
            $asset['version']
        );

        wp_enqueue_style(
            'bari-editor',
            APP_THEME_URI . '/dist/editor.css',
            [],
            $asset['version']
        );
    }

    /**
     * Enqueue assets that load in BOTH the editor and the frontend.
     * Use this for block-scoped CSS that needs to be consistent in both contexts.
     */
    public function enqueue_block_assets(): void {
        // Example:
        // wp_enqueue_style( 'bari-blocks', APP_THEME_URI . '/dist/blocks.css' );
    }

    // ─── Block Control ───────────────────────────────────────────────────────

    /**
     * Control which block types are available in the editor.
     *
     * All blocks are allowed by default. Projects restrict via the `bari/allowed_blocks` filter:
     *
     *   add_filter( 'bari/allowed_blocks', function ( $allowed, $context ) {
     *       return [
     *           'core/paragraph',
     *           'core/heading',
     *           'core/image',
     *           'core/group',
     *           'core/columns',
     *           'acf/hero',
     *       ];
     *   }, 10, 2 );
     *
     * @param bool|string[]          $allowed_blocks
     * @param \WP_Block_Editor_Context $block_editor_context
     * @return bool|string[]
     */
    public function allowed_block_types_all( $allowed_blocks, $block_editor_context ) {
        return apply_filters( 'bari/allowed_blocks', true, $block_editor_context );
    }

    /**
     * Prepend project-specific block categories to the editor's category list.
     * These map directly to the categories used in app/Patterns/*.php files.
     */
    public function block_categories_all( array $categories, $post ): array {
        $bari_categories = [
            [
                'slug'  => 'bari-sections',
                'title' => __( 'Sections', APP_THEME_DOMAIN ),
                'icon'  => 'layout',
            ],
            [
                'slug'  => 'bari-content',
                'title' => __( 'Content', APP_THEME_DOMAIN ),
                'icon'  => 'text',
            ],
            [
                'slug'  => 'bari-media',
                'title' => __( 'Media', APP_THEME_DOMAIN ),
                'icon'  => 'format-image',
            ],
        ];

        return array_merge( $bari_categories, $categories );
    }
}