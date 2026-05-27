<?php
/**
 * Tamarind Search Sidebar filter
 */

namespace tamarind_search;

defined( 'ABSPATH' ) || exit;

/**
 * Display Sidebar search
 *
 * @return string
 */
function sidebar_search(): string {
	$result = '<div class="position-sticky" style="top:50px;"
    >';
	$result .= '<section class="new-sidebar-filter-search">';

	$result .= search_group_field( 'Geography', 'geography' );

	$result .= search_group_field( 'Topic', 'topic' );
	$result .= search_group_field( 'Content type', 'content_type' );

	$result .= search_by_date();

	$result .= '</section>';
	$result .= '</div>';

	return $result;
}

function search_by_date(): string {
	return '
	<div id="search-dates" class="new-search-group">
	<h3 class="new-search-group-name">Date range <span class="fa fa-chevron-down icono-filtro girar" data-value="dates"></span></h3>
	  <div class="filter-search-select" data-value="dates" data-type="checkboxes">
		  <div class="option-search active filter-data" data-value="all" data-level="100">All</div>
		  <div class="option-search filter-data" data-value="1month" data-level="1">Last 30 days</div>	
		  <div class="option-search filter-data" data-value="thisyear" data-level="12">This year</div>	
		  <div class="option-search filter-data" data-value="3month" data-level="3">Last quarter</div>	
		  <div class="option-search filter-data" data-value="1year" data-level="12">Last year</div>	
	  </div>
	</div>
	';
}

function search_group_field( string $groupName, string $groupNameSlug ): string {
	return '
	<div id="search-' . $groupNameSlug . '" class="new-search-group">
	<h3 class="new-search-group-name">' . $groupName . '<span class="fa fa-chevron-down icono-filtro" data-value="' . $groupNameSlug . '"></span></h3>
	  <div class="filter-search-select" data-value="' . $groupNameSlug . '" data-type="checkboxes">
		  <div class="dummy-filter-search option-search" data-type="' . $groupNameSlug . '" data-value="dummy" hidden>Dummy Topic Search</div>
	  </div>
	</div>
	';
}
