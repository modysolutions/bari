<?php

namespace tamarind_wp_cli;

use WP_CLI;

if ( defined( 'WP_CLI' ) && \WP_CLI ) {
	WP_CLI::add_command( 'tamarind report', __NAMESPACE__ . '\Report_Handler' );
	WP_CLI::add_command( 'tamarind report all-users-access', __NAMESPACE__ . '\export_all_users_access' );
}

function export_all_users_access( array $args, array $assoc_args ): void {
	$handler = new Report_Handler();
	$handler->all_users_access( $args, $assoc_args );
}

class Report_Handler {
	private const DEFAULT_HEADERS = array(
		'Client',
		'Client ID',
		'User email',
		'User ID',
		'User registration date',
		'Platform',
		'Subscription',
	);

	/**
	 * Export the monthly all users access report.
	 *
	 * ## OPTIONS
	 *
	 * [--output=<path>]
	 * : Output CSV path. Relative paths are resolved from the WordPress root.
	 *
	 * [--platform-label=<label>]
	 * : Platform label to write in the CSV. Defaults to the site name without spaces.
	 *
	 * ## EXAMPLES
	 *
	 *     wp tamarind report all-users-access
	 *     wp tamarind report all-users-access --platform-label=ECigIntelligence --output=wp-content/uploads/reports/all-users-access-report-ecig.csv
	 *
	 * @param array $args Positional args.
	 * @param array $assoc_args Named args.
	 * @return void
	 */
	public function all_users_access( array $args, array $assoc_args ): void {
		$platform_label = $this->resolve_platform_label( (string) ( $assoc_args['platform-label'] ?? '' ) );
		$output_path    = $this->resolve_output_path( (string) ( $assoc_args['output'] ?? '' ), $platform_label );
		$directory      = dirname( $output_path );

		if ( ! is_dir( $directory ) && ! wp_mkdir_p( $directory ) ) {
			WP_CLI::error( sprintf( 'Unable to create directory: %s', $directory ) );
		}

		$csv = fopen( $output_path, 'w' );
		if ( false === $csv ) {
			WP_CLI::error( sprintf( 'Unable to open output file: %s', $output_path ) );
		}

		fputcsv( $csv, self::DEFAULT_HEADERS );

		$users = get_users( array(
			'role'    => 'client',
			'orderby' => 'user_email',
			'order'   => 'ASC',
		) );

		$count = 0;
		foreach ( $users as $user ) {
			$row = $this->build_row( $user, $platform_label );
			fputcsv( $csv, $row );
			$count++;
		}

		fclose( $csv );

		WP_CLI::success( sprintf( 'Exported %d users to %s', $count, $output_path ) );
	}

	private function build_row( \WP_User $user, string $platform_label ): array {
		$client_context = $this->resolve_client_context( (int) $user->ID );
		$plan_context   = $this->resolve_plan_context( (int) $user->ID, $client_context['client_post_id'] );

		return array(
			$client_context['client_name'],
			$client_context['client_export_id'],
			$user->user_email,
			(int) $user->ID,
			(string) $user->user_registered,
			$platform_label,
			$plan_context['plan_slug'],
		);
	}

	private function resolve_platform_label( string $platform_label ): string {
		if ( '' !== trim( $platform_label ) ) {
			return trim( $platform_label );
		}

		$site_name = (string) get_bloginfo( 'name' );
		$site_name = preg_replace( '/[^A-Za-z0-9]/', '', $site_name );

		return '' !== $site_name ? $site_name : 'Platform';
	}

	private function resolve_output_path( string $output_path, string $platform_label ): string {
		if ( '' === trim( $output_path ) ) {
			$timestamp       = current_time( 'Y_m_d_H_i_s' );
			$platform_suffix = sanitize_title( $platform_label );
			$output_path     = sprintf(
				'%s/data/all-users-access-report-%s-%s.csv',
				untrailingslashit( dirname( __DIR__ ) ),
				$platform_suffix,
				$timestamp
			);
		}

		if ( str_starts_with( $output_path, '/' ) ) {
			return $output_path;
		}

		return untrailingslashit( ABSPATH ) . '/' . ltrim( $output_path, '/' );
	}

	private function resolve_client_context( int $user_id ): array {
		$client_post_id  = $this->normalize_single_meta_id( get_user_meta( $user_id, 'related_client', true ) );
		$legacy_term_id  = $this->normalize_single_meta_id( get_user_meta( $user_id, 'clientes', true ) );
		$client_name     = '';
		$client_export_id = '';

		if ( $client_post_id <= 0 && $legacy_term_id > 0 ) {
			$client_post_id = $this->find_client_post_id_by_old_term_id( $legacy_term_id );
		}

		if ( $client_post_id > 0 ) {
			$client_name = get_the_title( $client_post_id );
			$old_term_id = (int) get_field( 'old_term_id', $client_post_id );
			$client_export_id = $old_term_id > 0 ? $old_term_id : $client_post_id;
			if ( '' === $client_name && $legacy_term_id > 0 ) {
				$client_name = $this->get_legacy_client_name( $legacy_term_id );
			}
		} elseif ( $legacy_term_id > 0 ) {
			$client_name      = $this->get_legacy_client_name( $legacy_term_id );
			$client_export_id = $legacy_term_id;
		}

		return array(
			'client_post_id'   => $client_post_id,
			'client_name'      => '' !== $client_name ? html_entity_decode( wp_specialchars_decode( $client_name ), ENT_QUOTES ) : 'N/A',
			'client_export_id' => '' !== (string) $client_export_id ? (string) $client_export_id : '',
		);
	}

	private function resolve_plan_context( int $user_id, int $client_post_id ): array {
		$plan_id = $this->normalize_single_meta_id( get_user_meta( $user_id, 'related_subscription_plan', true ) );
		if ( $plan_id <= 0 && $client_post_id > 0 ) {
			$plan_id = (int) get_field( 'subscription_plan', $client_post_id );
		}

		if ( $plan_id <= 0 ) {
			return array(
				'plan_id'   => 0,
				'plan_slug' => '',
			);
		}

		$plan_slug = (string) get_field( 'plan_slug', $plan_id );
		if ( '' === $plan_slug ) {
			$plan_slug = (string) get_post_field( 'post_name', $plan_id );
		}

		return array(
			'plan_id'   => $plan_id,
			'plan_slug' => $plan_slug,
		);
	}

	private function find_client_post_id_by_old_term_id( int $legacy_term_id ): int {
		$clients = get_posts( array(
			'post_type'      => 'client',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => 'old_term_id',
					'value'   => $legacy_term_id,
					'compare' => '=',
				),
			),
		) );

		return (int) ( $clients[0] ?? 0 );
	}

	private function get_legacy_client_name( int $legacy_term_id ): string {
		$term = get_term( $legacy_term_id, 'clientes' );

		if ( $term && ! is_wp_error( $term ) ) {
			return (string) $term->name;
		}

		return '';
	}

	private function normalize_single_meta_id( $value ): int {
		if ( is_array( $value ) ) {
			$value = reset( $value );
		}

		return absint( $value );
	}
}
