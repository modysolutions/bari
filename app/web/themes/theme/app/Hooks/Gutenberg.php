<?php

namespace App\Hooks;

class Gutenberg {
	public function init() : void {
		$this->action();
		$this->filter();
	}

	public function action() : void {
		add_action('after_setup_theme', [ $this, 'after_setup_theme']);
		add_action('enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets']);
	}

	public function filter() : void {
        add_filter('allowed_block_types_all', [$this, 'allowed_block_types_all'], 10, 2);
        add_filter('block_categories_all', [$this, 'block_categories_all'], 10, 2);
	}

	public function after_setup_theme() : void {
		remove_theme_support('core-block-patterns');
	}

	public function enqueue_block_editor_assets() : void {
		$editor_file = APP_THEME_DIR . '/dist/editor.asset.php';
        if(!file_exists($editor_file)) {
            return;
        }
        $editor = require_once $editor_file;
		wp_enqueue_script(
			'app-editor',
            APP_THEME_URI . '/dist/editor.js',
			$editor['dependencies'],
			$editor['version']
		);
        wp_enqueue_style(
            'app-editor',
            APP_THEME_URI . '/dist/editor.css',
            $editor['dependencies'],
            $editor['version']
        );
	}

	/**
	 * Control which block types are allowed in the editor.
	 *
	 * By default every block is allowed. Use the `bari/allowed_blocks` filter
	 * in your project to restrict the list to a specific set:
	 *
	 *   add_filter( 'bari/allowed_blocks', function ( $allowed, $context ) {
	 *       return [
	 *           'core/paragraph',
	 *           'core/heading',
	 *           'core/image',
	 *           'acf/hero',
	 *       ];
	 *   }, 10, 2 );
	 *
	 * @param bool|string[]                               $allowed_blocks
	 * @param \WP_Block_Editor_Context                    $block_editor_context
	 * @return bool|string[]
	 */
	public function allowed_block_types_all( $allowed_blocks, $block_editor_context ) {
		return apply_filters( 'bari/allowed_blocks', true, $block_editor_context );
	}

	public function block_categories_all(array $categories, $post) : array {
		return array_merge(
			$categories,
			array(
				array(
					'slug' => 'content',
					'title' => __( 'Content', 'content'  ),
				),
			),
			array (
				array(
					'slug' => 'media-blocks',
					'title' => __( 'Media', 'media-blocks'  ),
				),
			)
		);
	}
}