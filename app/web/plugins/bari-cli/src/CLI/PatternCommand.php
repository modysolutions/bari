<?php

namespace BariCli\CLI;

/**
 * Manages Gutenberg block patterns for the active theme.
 *
 * ## EXAMPLES
 *
 *   wp pattern create sections/feature-grid
 *   wp pattern export 42 sections/feature-grid
 *   wp pattern list
 */
class PatternCommand {

    // ─── Commands ────────────────────────────────────────────────────────────

    /**
     * Scaffold an empty pattern PHP file in the active theme's app/Patterns/ directory.
     *
     * ## ARGUMENTS
     *
     * <slug>
     * : Pattern path relative to app/Patterns/ without the .php extension.
     *   Slashes create subdirectories (e.g. "sections/hero").
     *
     * ## EXAMPLES
     *
     *   wp pattern create sections/hero
     *   wp pattern create content/two-column
     *
     * @subcommand create
     *
     * @param string[] $args
     * @param string[] $assoc_args
     */
    public function create( array $args, array $assoc_args ): void {
        if ( empty( $args[0] ) ) {
            \WP_CLI::error( 'Please provide a slug. Example: wp pattern create sections/hero' );
        }

        $slug     = trim( $args[0], '/' );
        $filepath = $this->pattern_path( $slug );

        if ( file_exists( $filepath ) ) {
            \WP_CLI::error( "Pattern file already exists: {$filepath}" );
        }

        $this->ensure_dir( dirname( $filepath ) );

        $title = ucwords( str_replace( [ '/', '-', '_' ], ' ', basename( $slug ) ) );

        file_put_contents( $filepath, $this->stub( $title ) );

        \WP_CLI::success( "Pattern created: {$filepath}" );
        \WP_CLI::log( \WP_CLI::colorize( "%YNext step:%n Design in Gutenberg → open Code Editor → copy block markup → paste into the 'content' key." ) );
    }

    /**
     * Export a post's block content as a pattern file in the active theme.
     *
     * ## ARGUMENTS
     *
     * <post-id>
     * : ID of the post/page whose block markup should be exported.
     *
     * <slug>
     * : Pattern path relative to app/Patterns/ without the .php extension.
     *
     * ## OPTIONS
     *
     * [--title=<title>]
     * : Human-readable pattern title. Defaults to the post title.
     *
     * [--category=<category>]
     * : Pattern category slug. Default: bari-sections
     * ---
     * default: bari-sections
     * options:
     *   - bari-sections
     *   - bari-content
     *   - bari-media
     *   - bari-exports
     * ---
     *
     * [--overwrite]
     * : Overwrite the file if it already exists.
     *
     * ## EXAMPLES
     *
     *   wp pattern export 42 sections/hero
     *   wp pattern export 42 sections/hero --title="Hero – Full Width" --overwrite
     *
     * @subcommand export
     *
     * @param string[]              $args
     * @param array<string, string> $assoc_args
     */
    public function export( array $args, array $assoc_args ): void {
        if ( count( $args ) < 2 ) {
            \WP_CLI::error( 'Usage: wp pattern export <post-id> <slug>' );
        }

        $post_id  = (int) $args[0];
        $slug     = trim( $args[1], '/' );
        $post     = get_post( $post_id );

        if ( ! $post ) {
            \WP_CLI::error( "Post #{$post_id} not found." );
        }

        $content = $post->post_content;

        if ( empty( trim( $content ) ) ) {
            \WP_CLI::error( "Post #{$post_id} has no content to export." );
        }

        $filepath = $this->pattern_path( $slug );

        if ( file_exists( $filepath ) && ! \WP_CLI\Utils\get_flag_value( $assoc_args, 'overwrite', false ) ) {
            \WP_CLI::error( "File already exists. Use --overwrite to replace it: {$filepath}" );
        }

        $title    = \WP_CLI\Utils\get_flag_value( $assoc_args, 'title', $post->post_title );
        $category = \WP_CLI\Utils\get_flag_value( $assoc_args, 'category', 'bari-sections' );

        $this->ensure_dir( dirname( $filepath ) );

        file_put_contents( $filepath, $this->stub( $title, $category, $content ) );

        \WP_CLI::success( "Pattern exported from post #{$post_id} to: {$filepath}" );
    }

    /**
     * List all pattern PHP files found in the active theme's app/Patterns/ directory.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     * ---
     *
     * ## EXAMPLES
     *
     *   wp pattern list
     *   wp pattern list --format=json
     *
     * @subcommand list
     *
     * @param string[]              $args
     * @param array<string, string> $assoc_args
     */
    public function list( array $args, array $assoc_args ): void {
        $patterns_dir = $this->patterns_dir();

        if ( ! is_dir( $patterns_dir ) ) {
            \WP_CLI::log( \WP_CLI::colorize( '%YNo patterns directory found at:%n ' . $patterns_dir ) );
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator( $patterns_dir, \FilesystemIterator::SKIP_DOTS )
        );

        $rows = [];

        foreach ( $iterator as $file ) {
            if ( $file->getExtension() !== 'php' ) {
                continue;
            }

            $data     = @include $file->getPathname();
            $relative = str_replace( $patterns_dir . DIRECTORY_SEPARATOR, '', $file->getPathname() );

            $rows[] = [
                'file'       => $relative,
                'title'      => is_array( $data ) ? ( $data['title'] ?? '—' ) : '⚠ invalid',
                'categories' => is_array( $data ) ? implode( ', ', $data['categories'] ?? [] ) : '—',
                'size'       => round( $file->getSize() / 1024, 1 ) . ' KB',
            ];
        }

        if ( empty( $rows ) ) {
            \WP_CLI::log( \WP_CLI::colorize( '%YNo pattern files found.%n' ) );
            return;
        }

        $format = \WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'table' );
        \WP_CLI\Utils\format_items( $format, $rows, [ 'file', 'title', 'categories', 'size' ] );
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function patterns_dir(): string {
        return get_template_directory() . '/app/Patterns';
    }

    private function pattern_path( string $slug ): string {
        return $this->patterns_dir() . '/' . ltrim( $slug, '/' ) . '.php';
    }

    private function ensure_dir( string $dir ): void {
        if ( ! is_dir( $dir ) ) {
            mkdir( $dir, 0755, true );
        }
    }

    /**
     * Generate PHP source for a pattern file.
     *
     * @param string $title    Pattern title.
     * @param string $category Block pattern category slug.
     * @param string $content  Serialised block markup (empty for scaffold).
     */
    private function stub( string $title, string $category = 'bari-sections', string $content = '' ): string {
        $content_placeholder = $content
            ? addslashes( $content )
            : "<!-- wp:paragraph -->\n<p>Replace this placeholder with your exported block markup.</p>\n<!-- /wp:paragraph -->";

        return <<<PHP
<?php

/**
 * Pattern: {$title}
 *
 * To update the content:
 *   1. Design in the Gutenberg editor.
 *   2. Run: ./bin/wp pattern export <post-id> <slug> --overwrite
 *   OR open the Code Editor (⇧⌥⌘M), copy the markup and paste below.
 */

return [
    'title'         => __( '{$title}', 'theme' ),
    'description'   => '',
    'categories'    => [ '{$category}' ],
    'keywords'      => [],
    'viewportWidth' => 1440,
    'content'       => <<<BLOCKS
{$content_placeholder}
BLOCKS,
];
PHP;
    }
}

