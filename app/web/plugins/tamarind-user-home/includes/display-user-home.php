<?php

/**
 * Display User Home Modules
 *
 * @package Tamarind_UserHome
 */

namespace tamarind_userhome;

defined('ABSPATH') || exit;

/**
 * Display the User Home Layout.
 *
 * @return void
 */
function display_user_home()
{ ?>

    <div id="user-home-content" class="tm-layout-main tm-layout-wrapper tm-layout-main--sidebar tm-layout-main--sidebar-right">
        <main class="tm-layout-main__content">
            <?php
            // Display the User Home Modules.
            display_user_home_modules('tm_userhome_flexible_content_modules'); ?>

        </main>
        <aside class="tm-layout-main__aside">
            <?php
            // Display the User Home Sidebar.
            display_user_home_modules('tm_userhome_flexible_content_sidebar'); ?>
        </aside>
    </div>
    <?php
}


/**
 * Display the User Home Modules.
 *
 * @param string $flexible_field_name Flexible field name (tm_userhome_flexible_content_modules or tm_userhome_flexible_content_sidebar).
 * @return void
 */
function display_user_home_modules($flexible_field_name = 'tm_userhome_flexible_content_modules')
{
    // Check if the Flexible field exists
    if (have_rows($flexible_field_name, 'option')) {
        // Loop through each row of the Flexible field
        while (have_rows($flexible_field_name, 'option')) {
            the_row();

            // Get the current layout
            $layout = get_row_layout();

            // Define the template path
            $position = ($flexible_field_name == 'tm_userhome_flexible_content_modules') ? 'module_' : 'sidebar_';
            $template_path = plugin_dir_path(__FILE__) . '../templates/' . $position . $layout . '.php';

            // Check if the template file exists
            if (file_exists($template_path)) {
                // Include the template
                include $template_path;
            } else {
                // Show an error message if the template does not exist
                echo '<p>Error: Template not found for layout "' . esc_html($layout) . '".</p>';
            }
        }
    } else {
        // Show a message if no modules are configured
        echo '<p>No modules configured for this section.</p>';
    }
}
