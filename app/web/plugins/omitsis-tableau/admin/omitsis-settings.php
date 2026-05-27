<?php

/**
 * Handle common Functions for admin settings
 *
 * @package Omitsis_Data_Tamarind_Api
 */

 namespace omitsis\tableau_admin_settings;

 const group_settings = 'omitsis_tableau_settings';
 const id_settings_section = 'omitsis_tableau_settings_id';


function render_settings_section (){
	echo _( 'This is the hub for configuring vital plugin settings, allowing you to fine-tune its functionality to suit your specific requirements and preferences effectively'  );
}

function render_delete_data_field(){
	$checkbox = get_option( 'omitsis_tableau_settings_delete' );
    echo '<input type="checkbox" name="omitsis_tableau_settings_delete" value="1" ' . checked(1, $checkbox, false) . ' />';
}

/**
 * The function "omitsis_options_update" is used to register and sanitize settings for a plugin in PHP.
 */
 function omitsis_options_update () {

	add_settings_section(
		id_settings_section,
		'Omitsis Tableau General Options',
		__NAMESPACE__ . '\render_settings_section',
		group_settings,
		array(
			'before_section' => '', //html for before the section
			'after_section' => '', //html for after the section
		)
	);

	add_settings_field(
		'omitsis_tableau_settings_delete',
		'Database delete data',
		__NAMESPACE__ . '\render_delete_data_field',
		group_settings,
		id_settings_section
	);
	register_setting( group_settings, 'omitsis_tableau_settings_delete' );

 }

 /**
  * The function `omitsis_data_api_settings` displays a form with options for managing database tables
  * and clearing plugin data on deactivation.
  */
 function omitsis_tableau_settings () {
	echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
	?>
	<div class="wrap">
		<form class="omitsis_settings_admin {" method="post" action="options.php">
			<?php
			settings_fields(group_settings);
            do_settings_sections(group_settings);
            submit_button('Save all changes', 'primary', 'submit', TRUE); ?>
		</form>
	</div>
	<?php
 }
