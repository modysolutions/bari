<?php
/**
 * Template for Alerts Filter by country/region module sidebar.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\alerts;

defined( 'ABSPATH' ) || exit;

// Get the layout values.
$title_module    = __( 'By country/region', 'tamarind-base' );
$container_style = 'tm-sidebar-module--light';

// Get Geography terms.
$geo_group = \tamarind_base\taxonomies\alerts_europe_us_news();

if ( ! empty( $geo_group ) ) {
	$geo = $geo_group;

	$geo_terms_continent = get_terms(
		'geography',
		array(
			'hide_empty' => 0,
			'slug'       => $geo,
		)
	);

	$id_geo = '';
	foreach ( $geo_terms_continent as $geo_term_continent ) {
		$id_geo = $geo_term_continent->term_id;
	}

	$geoterms = get_terms(
		'geography',
		array(
			'hide_empty' => 0,
			'parent'     => $id_geo,
		)
	);
} else {
	$geoterms = get_terms(
		array(
			'taxonomy'   => 'geography',
			'hide_empty' => true,
			'orderby'    => 'name',
		)
	);
}
?>

<section class="alerts-filter-sidebar tm-sidebar-module <?php echo esc_attr( $container_style ); ?>">

	<h2 class="tm-sidebar-module__title"><?php echo esc_html( $title_module ); ?></h2>

	<form action="/regulatory-alerts/" method="get" class="form-geo-alerts">	

		<select name="geoalerts" id="geoalerts">
			<option value=""><?php esc_attr_e( 'Global alerts', 'tamarind-base' ); ?></option>
			<optgroup label="<?php esc_attr_e( 'Filter:', 'tamarind-base' ); ?>">
				<?php

				foreach ( $geoterms as $geoterm ) {
					?>
					<option value="<?php echo esc_attr( $geoterm->slug ); ?>"><?php echo esc_attr( $geoterm->name ); ?></option>
					<?php
				}
				?>
			</optgroup>
		</select><button class="button-filter-geo"><?php esc_html_e( 'GO', 'tamarind-base' ); ?></button>
		<input type="hidden" name="filter-tax" value="geo" />
	</form>
</section>
