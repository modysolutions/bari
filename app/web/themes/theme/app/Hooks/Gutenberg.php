<?php

namespace App\Hooks;

class Gutenberg {
	public function init() : void {
		$this->action();
		$this->filter();
	}

	public function action() : void {
		add_action('after_setup_theme', [ $this, 'after_setup_theme']);
	}

	public function filter() : void {}

	public function after_setup_theme() : void {
		// Core FSE supports for template and editor parity.
		add_theme_support('post-thumbnails');
		add_theme_support('title-tag');
		add_theme_support('custom-logo', [
			'height' => 120,
			'width' => 400,
			'flex-height' => true,
			'flex-width' => true,
			'unlink-homepage-logo' => true,
		]);
		add_theme_support('block-templates');
		add_theme_support('block-template-parts');
		add_theme_support('editor-styles');
		add_theme_support('wp-block-styles');
		add_theme_support('responsive-embeds');
		add_theme_support('appearance-tools');

		add_editor_style('assets/css/editor-style.css');

		// Keep editor suggestions focused on project-owned patterns.
		remove_theme_support('core-block-patterns');
	}
}