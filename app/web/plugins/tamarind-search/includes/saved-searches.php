<?php

/**
 * Handle Save user search functionality
 *
 * @package Tamarind_Search
 */

namespace tamarind_search;

defined('ABSPATH') || exit;


/**
 * Save user search functionality.
 *
 * This function saves a user's search term and URL into an ACF repeater field.
 * It only works for logged-in users. If the search term already exists, it updates the last search date.
 *
 * @param string $search_word The search term entered by the user.
 * @param string $search_url  The full URL of the search.
 *
 * @return void
 */
function save_user_search( string $search_word, string $search_url): void {
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        return; 
    }

    // Get the current user ID
    $user_id = get_current_user_id();

    // Get the existing saved searches from the user's ACF repeater field
    $saved_searches = get_field('saved_searches', 'user_' . $user_id);

    // Convert the new search term to lowercase for case-insensitive comparison
    $search_word = mb_strtolower($search_word, 'UTF-8');

    // Initialize a flag to check if the search term already exists
    $search_exists = false;

    // Loop through the saved searches to check if the search term already exists
    if ($saved_searches) {
        foreach ($saved_searches as $key => $search) {
            if ($search['search_word'] === $search_word) {
                // Update the last_search date for the existing search term
                $saved_searches[$key]['last_search'] = date('Y-m-d H:i:s');

                // Increment the search_counter by 1
                $saved_searches[$key]['search_counter'] = isset($search['search_counter']) ? $search['search_counter'] + 1 : 1;

                $search_exists = true;
                break;
            }
        }
    }

    // If the search term does not exist, add it to the saved searches
    if (!$search_exists) {
        $new_search = array(
            'search_word'    => $search_word,
            'search_url'     => $search_url,
            'last_search'   => date('Y-m-d H:i:s'),
            'search_counter' => 1, 
        );

        // Add the new search to the saved searches array
        $saved_searches[] = $new_search;
    }

    // Update the user's ACF repeater field with the modified saved searches
    update_field('saved_searches', $saved_searches, 'user_' . $user_id);
}

/**
 * Add Saved Searches Link.
 *
 * This function adds a link to the saved searches page in the search layout.
 *
 * @param string $id The unique ID of the search layout.
 * @return void
 */
add_action('asp_layout_after_magnifier', function ($id) {

    // TODO: add_action in top of the file and create this function
    // TODO: remove CSS Styles from here to assets/css/saved-searches.css

    // Check if the user is logged in
    if (!is_user_logged_in()) {
        return; 
    }

    $my_searches_url = get_menu_link_by_key('saved_searches'); ?>

    <a href="<?php echo esc_url($my_searches_url); ?>" id="saved-searches-<?php echo esc_attr($id); ?>" class="saved-searches-link" title="My search history" style="order:12;">

        <?php echo \tamarind_base\get_svg_icon('saved-searches', '', 'Search history'); ?>
        
    </a>
    <style>
        .saved-searches-link {
            display: inline-block;
            margin-left: 8px;
            vertical-align: middle;
        }

        .saved-searches-link svg {
            width: 24px;
            height: 24px;
            transition: fill 0.3s;
        }

        .saved-searches-link:hover svg {
            fill: #005b8e;
        }
    </style>
<?php
}, 20, 1);


/**
 * Display User Saved Searches.
 *
 * This function retrieves and displays the user's saved searches in a table format.
 * Each row includes the search word, search URL, last search date, and a delete icon.
 * Searches are ordered by date, with the most recent first.
 *
 * @return string The HTML content of the saved searches table.
 */
function display_user_saved_searches(): string {
    // Start output buffering
    ob_start();

    // Check if the user is logged in
    if (!is_user_logged_in()) {
        echo '<p>You must be logged in to view saved searches.</p>';
        return ob_get_clean(); 
    }

    // Get the current user ID
    $user_id = get_current_user_id();

    // Get the saved searches from the user's ACF repeater field
    $saved_searches = get_field('saved_searches', 'user_' . $user_id);

    // Check if there are any saved searches
    if (empty($saved_searches)) {
        echo '<p>No saved searches found.</p>';
        return ob_get_clean(); 
    }

    // Sort searches by last_search date (most recent first)
    usort($saved_searches, function ($a, $b) {
        return strtotime($b['last_search']) - strtotime($a['last_search']);
    });

    // Start the table
    echo '<table class="saved-searches-table">';
    echo '<thead>
            <tr>
                <th>Search phrases</th>
                <th>Last Search</th>
                <th>Counter</th>
                <th>Remove</th>
            </tr>
          </thead>';
    echo '<tbody>';

    // Loop through the saved searches and display each one
    foreach ($saved_searches as $index => $search) {
        echo '<tr id="search-row-' . esc_attr($index) . '">'; // Unique ID based on index
        echo '<td><a href="' . esc_url($search['search_url']) . '" target="_blank">';
        echo esc_html($search['search_word']) . ' ';
        echo \tamarind_base\get_svg_icon('link', '', 'Open link search');
        echo '</a></td>';
        echo '<td>' . esc_html($search['last_search']) . '</td>';
        echo '<td>' . esc_html($search['search_counter']) . '</td>';
        echo '<td>
                <a href="#" class="delete-search" data-search-word="' . esc_attr($search['search_word']) . '" data-row-index="' . esc_attr($index) . '">
                    ' . \tamarind_base\get_svg_icon('delete', '', 'remove search' ) . '
                </a>
              </td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    // Return the buffered content
    return ob_get_clean();
}

/**
 * AJAX Handler to Delete a User Search.
 *
 * This function handles the AJAX request to delete a saved search from the user's ACF repeater field.
 *
 * @return void
 */
function delete_user_search(): void {
    // Verify the nonce for security
    if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'delete_search_nonce')) {
        wp_send_json_error('Invalid nonce.');
    }

    // Check if the user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in.');
    }

    // Get the current user ID
    $user_id = get_current_user_id();

    // Get the search word to delete
    $search_word = isset($_POST['search_word']) ? sanitize_text_field($_POST['search_word']) : '';

    if (empty($search_word)) {
        wp_send_json_error('Search word is required.');
    }

    // Get the saved searches from the user's ACF repeater field
    $saved_searches = get_field('saved_searches', 'user_' . $user_id);

    // Filter out the search to delete
    $updated_searches = array_filter($saved_searches, function ($search) use ($search_word) {
        return $search['search_word'] !== $search_word;
    });

    // Update the user's ACF repeater field
    update_field('saved_searches', array_values($updated_searches), 'user_' . $user_id);

    wp_send_json_success('Search deleted successfully.');
}
add_action('wp_ajax_delete_user_search', __NAMESPACE__ . '\delete_user_search');
