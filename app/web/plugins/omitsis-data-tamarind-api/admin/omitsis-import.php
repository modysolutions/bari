<?php

/**
 * Import CSV common functions
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace data\import;

global $wpdb;
global $features_slugs;

define( 'PREFIX_TABLE_PLUGIN_IMPORT', $wpdb->prefix . 'omitsis_data_api_' );
defined( 'ABSPATH' ) || exit;

/**
 * The function creates a directory named "omitsis-data-api" inside the WordPress uploads directory if
 * it doesn't already exist.
 */
function omitsis_create_directory() {
	$upload_dir = wp_upload_dir();
	$target_dir = $upload_dir['basedir'] . '/omitsis-data-api';

	if ( ! file_exists( $target_dir ) ) {
		mkdir( $target_dir, 0777, true );
	}
}

/**
 * The function `omitsis_move_uploaded_file` checks if the uploaded file is of type text/csv, creates a
 * directory if it doesn't exist, renames the file if it already exists, and moves the uploaded file to
 * the target directory.
 *
 * @param file The parameter `` is an array that contains information about the uploaded file. It
 * typically includes the following keys:
 *
 * @return the path of the uploaded file if it is successfully moved to the target directory. If the
 * file is not of type "text/csv" or if the file cannot be moved, the function returns false.
 */
function omitsis_move_uploaded_file( $file ) {

	// if file is not text/csv type return false
	if ( $file['type'] !== 'text/csv' ) {
		return false;
	}

	$upload_dir = wp_upload_dir();
	$target_file = $upload_dir['basedir'] . '/omitsis-data-api/' . basename($file['name']);

	omitsis_create_directory();

	// if target_file already exists, rename it
	if ( file_exists( $target_file ) ) {
		$target_file = $upload_dir['basedir'] . '/omitsis-data-api/' . uniqid() . '_' . basename($file['name']);
	}

	if (move_uploaded_file($file['tmp_name'], $target_file)) {
		return $target_file;
	}
	return false;
}

/**
 * The function checks if the given CSV data has the correct headers: 'country', 'region', 'year', and
 * 'total_market'.
 *
 * @param data The parameter `` is an array that represents a row of data from a CSV file. The
 * elements of the array correspond to the columns of the CSV file.
 *
 * @return a boolean value. If the conditions in the if statement are met, it will return true.
 * Otherwise, it will return false.
 */
function get_validate_csv_data ( $data ) {
	// the CSV can be validated very thoroughly, at the moment it is not necessary
	if ( 'country' !== $data[0] || 'region' !== $data[1] || 'year' !== $data[2] ) {
		return false;
	}

	return true;
}

/**
 * The function "get_feature_slugs" takes an array of data and adds values to the global variable
 * "features_slugs" if the key is greater than or equal to 40.
 *
 * @param data An array containing key-value pairs.
 *
 * @return a boolean value of true.
 */
function get_feature_slugs ( $data ) {

	global $features_slugs;

	foreach ( $data as $key => $value ) {
		// Init features on 3 position from csv file - country + region + year
		if ( 3 <= $key ) {
			$features_slugs[] = $value;
		}
	}
	return true;
}

/**
 * The function "extract_number" takes a string as input and returns a number extracted from that
 * string by removing any non-numeric characters.
 *
 * @param string The parameter "string" is a variable that represents the input string from which we
 * want to extract the number.
 *
 * @return the number extracted from the given string.
 */
function extract_number ( $string ) {
	$number = preg_replace('/[^0-9.]/', '', $string);
	$number = round_number ( $number );
	return $number;
}

/**
 * The function "round_number" rounds a given number to 5 decimal places in PHP.
 *
 * @param number The parameter "number" is a numeric value that you want to round.
 *
 * @return the rounded value of the input number.
 */
function round_number ( $number ) {
	$number = round( $number, 5 );
	return $number;
}

/**
 * The function `import_features` reads the uploaded CSV file and imports the data into the
 * database.
 *
 * @param target_file The parameter `` is the path of the uploaded CSV file.
 *
 * @return true if the data is successfully imported into the database. Otherwise, it returns false.
 */
function import_features ( $target_file ) {

	if (($handle = fopen($target_file, "r")) !== FALSE) {

		global $wpdb;
		$table_name	   = PREFIX_TABLE_PLUGIN_IMPORT . 'features';

		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

			// Skip if data is not complete
			if ( 4 !== count( $data ) ) {
				return false;
			}

			$slug_feature_name = $data[1];
			$sql = "SELECT * FROM $table_name WHERE slug_feature_name = '$slug_feature_name'";
			$result = $wpdb->get_results( $sql );

			if ( 0 < count( $result ) ) {
				// if slug_feature_name already exists, update all data
				$data_array_update = array(
					'feature_name' => $data[3],
					'feature_order' => $data[2],
					'feature_type' => $data[0],
				);

				$wpdb->update(
					$table_name,
					$data_array_update,
					array( 'slug_feature_name' => $slug_feature_name ),
				);
			} else {
				// if slug_feature_name does not exist, insert new data
				$data_array_insert = array(
					'feature_name' => $data[3],
					'slug_feature_name' => $data[1],
					'feature_order' => $data[2],
					'feature_type' => $data[0],
				);

				$wpdb->insert(
					$table_name,
					$data_array_insert,
				);
			}
		}
		fclose($handle);

		return true;
	}
	return false;
}

/**
 * The function imports a region into a database table if it doesn't already exist, and updates the
 * note field if it does.
 *
 * @param data The parameter `` is an array that contains the region name at index 1.
 */
function import_region ( $data ) {

	global $wpdb;
	$table_name	   = PREFIX_TABLE_PLUGIN_IMPORT . 'region';

	$region_name = $data[1];
	$sql = "SELECT * FROM $table_name WHERE name = '$region_name'";
	$result = $wpdb->get_results( $sql );
	// echo 'Data Region: ' . $data[1] . ' / ' . $sql . '<br />';

	if ( 0 < count( $result ) ) {
		$data_array_update = array(
			'note' => '',
		);

		$wpdb->update(
			$table_name,
			$data_array_update,
			array( 'name' => $region_name ),
		);
	} else {
		$data_array_insert = array(
			'name' => $region_name,
			'note' => '',
		);

		$wpdb->insert(
			$table_name,
			$data_array_insert,
		);
	}

	// return id of region
	$sql = "SELECT id FROM $table_name WHERE name = '$region_name'";
	$id = $wpdb->get_results( $sql );

	if ( 0 < count( $id ) ) {
		return $id[0]->id;
	}
	return '';
}

/**
 * The function `import_country` imports country data into a database table, updating existing records
 * or inserting new ones based on the provided data.
 *
 * @param data The parameter `` is an array that contains the information about the country to be
 * imported. It is expected to have two elements:
 */
function import_country ( $data, $region_id ) {

	global $wpdb;
	$table_name	   = PREFIX_TABLE_PLUGIN_IMPORT . 'country';

	$country_name = $data[0];
	$sql = "SELECT * FROM $table_name WHERE name = '$country_name'";

	$result = $wpdb->get_results( $sql );

	if ( 0 < count( $result ) ) {
		$data_array_update = array(
			'region_id' => $region_id,
		);

		$wpdb->update(
			$table_name,
			$data_array_update,
			array( 'name' => $country_name ),
		);
	} else {
		$data_array_insert = array(
			'name' => $country_name,
			'region_id' => $region_id,
		);

		$wpdb->insert(
			$table_name,
			$data_array_insert,
		);
	}

	// return id of country
	$sql = "SELECT id FROM $table_name WHERE name = '$country_name'";
	$id = $wpdb->get_results( $sql );

	if ( 0 < count( $id ) ) {
		return $id[0]->id;
	}
	return '';
}


function import_data ( $data, $country_id ) {

	global $wpdb;
	global $features_slugs;

	$table_name	   = PREFIX_TABLE_PLUGIN_IMPORT . 'meta_value';
	$year = $data[2];

	foreach ( $data as $key => $value ) {
		// Init features on 3 position from csv file - country + region + year
		if ( ( 3 <= $key ) && ( '' !== $value ) ) {

			$value_to_insert = is_numeric($value) ? extract_number($value) : $value;
			$data_array_update = array(
				'value' => $value_to_insert ,
			);

			$data_array_key = array(
				'country_id' => $country_id,
				'year' => $year,
				'slug_feature_name' => $features_slugs[$key - 3],
			);

			$update = exists_value_in_ddbb ( $table_name, $country_id, $year, $features_slugs[$key - 3] );
			insert_update_ddbb ( $update, $table_name, $data_array_key, $data_array_update );
		}

	}
}

/**
 * The function checks if a record exists in a database table based on the country ID and year.
 *
 * @param table_name The name of the table in the database where the data is stored.
 * @param country_id The country ID is a unique identifier for a specific country in the database. It
 * is used to filter the results and check if a record exists for a specific country.
 * @param year The "year" parameter is the year for which you want to check if a record exists in the
 * database.
 *
 * @return a boolean value. It returns true if there is at least one row in the specified table that
 * matches the given country_id and year, and false otherwise.
 */
function exists_in_ddbb ( $table_name, $country_id, $year ) {

	global $wpdb;

	$sql = "SELECT * FROM $table_name WHERE country_id = '$country_id' AND year = '$year'";
	$result = $wpdb->get_results( $sql );
	if ( 0 < count( $result ) ) {
		return true;
	}
	return false;
}

/**
 * The function `insert_update_ddbb` is used to insert or update data in a database table using the
 * WordPress database class ``.
 *
 * @param update The "update" parameter is a boolean value that determines whether to perform an insert
 * or an update operation. If it is set to true, an update operation will be performed. If it is set to
 * false, an insert operation will be performed.
 * @param table_name The name of the table in the database where the data will be inserted or updated.
 * @param data_array_key The `data_array_key` parameter is an associative array that contains the
 * key-value pairs for the primary key or unique identifier of the row you want to insert or update in
 * the database table. These key-value pairs specify the column names and their corresponding values
 * that uniquely identify the row.
 * @param data_array_update The `data_array_update` parameter is an associative array that contains the
 * data to be updated in the database table. The keys of the array represent the column names in the
 * table, and the values represent the new values to be updated.
 */
function insert_update_ddbb ( $update, $table_name, $data_array_key, $data_array_update ) {

	global $wpdb;

	if ( !$update ) {
		// data_array_insert is the same as data_array_update with country_id and year added
		$data_array_insert = array_merge( $data_array_update, $data_array_key );
		$wpdb->insert(
			$table_name,
			$data_array_insert,
		);
	} else {
		$wpdb->update(
			$table_name,
			$data_array_update,
			$data_array_key,
		);
	}

}

/**
 * The function checks if a pricing entry exists in a database table based on the country ID, year, and
 * feature name.
 *
 * @param table_name The name of the table in the database where the pricing information is stored.
 * @param country_id The country ID is a unique identifier for a specific country in the database. It
 * is used to filter the pricing data based on the country.
 * @param year The year parameter is used to specify the year for which you want to check if pricing
 * data exists in the database.
 * @param slug_feature_name The slug_feature_name parameter is a string that represents the feature
 * name in a URL-friendly format. It is used as a filter in the SQL query to check if there is pricing
 * data for a specific feature.
 *
 * @return a boolean value. It returns true if there is pricing data in the database that matches the
 * given parameters, and false otherwise.
 */
function exists_value_in_ddbb ( $table_name, $country_id, $year, $slug_feature_name ) {

	global $wpdb;

	$sql = "SELECT * FROM $table_name WHERE country_id = '$country_id' AND year = '$year' AND slug_feature_name = '$slug_feature_name'";
	$result = $wpdb->get_results( $wpdb->prepare( $sql ) );
	if ( 0 < count( $result ) ) {
		return true;
	}
	return false;
}

/**
 * The function imports data from a CSV file into various database tables.
 *
 * @param target_file The target_file parameter is the file path of the CSV file that contains the data
 * to be imported.
 *
 * @return a boolean value. It returns true if the file was successfully imported and false if there
 * was an error or if the data in the file is not complete.
 */
function import_all_data ( $target_file ) {

	global $features_slugs;

	if (($handle = fopen($target_file, "r")) !== FALSE) {
		global $wpdb;

		while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {

			// skip first line
			if ( 'country' === $data[0] ) {
				// init features_slugs array define in first line of csv file

				if ( !get_validate_csv_data( $data ) ) {
					// if data is not complete
					return false;
				}
				get_feature_slugs ( $data );
				continue;
			}

			// import data into region table
			$region_id = import_region ( $data );

			// import data into country table
			$country_id = import_country ( $data, $region_id );

			// // import data into pricing table
			import_data ( $data, $country_id );

		}
		fclose($handle);

		return true;
	}
	return false;
}

/**
 * The function imports data from a CSV file into a country table, skipping incomplete data and the
 * first line.
 *
 * @param target_file The parameter "target_file" is the file path of the CSV file that contains the
 * countries data to be imported.
 *
 * @return a boolean value. It returns true if the file is successfully imported and false if there is
 * an error or if the data in the file is not complete.
 */
function import_countries_data ( $target_file ) {

	if (($handle = fopen($target_file, "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {

			// Skip if data is not complete
			// code_country / name / char / currency
			if ( 4 !== count( $data ) ) {
				return false;
			}

			// skip first line from countries
			if ( 'id' === $data[0] ) {
				continue;
			}

			// import data into country table
			import_and_update_country ( $data );
		}
		fclose($handle);

		return true;
	}
	return false;
}

/**
 * The function imports and updates country data in a database table if the country already exists.
 *
 * @param data The parameter `` is an array that contains the information about the country to be
 * imported and updated. It is assumed that the array has the following structure:
 *
 * @return a boolean value of true.
 */
function import_and_update_country ( $data ) {

	global $wpdb;
	$table_name	   = PREFIX_TABLE_PLUGIN_IMPORT . 'country';

	$country_name = $data[1];
	$sql = "SELECT * FROM $table_name WHERE name = '$country_name'";

	$result = $wpdb->get_results( $sql );

	if ( 0 < count( $result ) ) {
		$data_array_update = array(
			'code' => $data[0],
			'code_char' => $data[2],
			'currency' => $data[3],
		);

		$wpdb->update(
			$table_name,
			$data_array_update,
			array( 'name' => $country_name ),
		);
	} else {
		$data_array_insert = array(
			'code' => $data[0],
			'name' => $data[1],
			'code_char' => $data[2],
			'currency' => $data[3],
		);

		$wpdb->insert(
			$table_name,
			$data_array_insert,
		);
	}

	return true;
}

/**
 * The function `form_submit_file` handles the submission of a file, checks if it is a CSV file, moves
 * it to a target location, and then imports the data from the file if it is in the correct format.
 *
 * @param file_name The parameter `` is the name of the file input field in the HTML form. It
 * is used to identify the uploaded file in the `` superglobal array.
 *
 * @return a message indicating the status of the file upload and data import process.
 */
function form_submit_file ( $file_name ) {

	if ( isset($_FILES[$file_name]) ) {

		$file        = $_FILES[$file_name];
		$target_file = omitsis_move_uploaded_file( $file );

		if ( false !== $target_file ) {

			switch ( $file_name ) {
				case 'file_countries':
					$import_status = import_countries_data ( $target_file );
					break;
				case 'file_features':
					$import_status = import_features ( $target_file );
					break;
				case 'file_all_data':
					$import_status =  import_all_data ( $target_file );
					break;
				default:
					$message = '<b>Error:</b> File type is not CSV or bad upload.<br />';
			}

			$message = 'File: <b>' . $file['name'] . '</b><br />Uploaded successfully. <br />';
			if ( $import_status ) {
				$message .= '<b>Data imported.</b>';
			} else {
				$message .= '<b>Error importing data. </b><br />Format of CSV file is not correct.';
			}
		} else {
			$message =  '<b>Error:</b> File type is not CSV or bad upload.';
		}

		return $message;
	}
	return false;
}

/**
 * Delete all transients from the database whose keys have a specific prefix.
 *
 * @param string $prefix The prefix. Example: 'my_cool_transient_'.
 */
function delete_transients_with_prefix( $prefix ) {
	foreach ( get_transient_keys_with_prefix( $prefix ) as $key ) {
		delete_transient( $key );
	}
}

/**
 * Gets all transient keys in the database with a specific prefix.
 *
 * Note that this doesn't work for sites that use a persistent object
 * cache, since in that case, transients are stored in memory.
 *
 * @param  string $prefix Prefix to search for.
 * @return array          Transient keys with prefix, or empty array on error.
 */
function get_transient_keys_with_prefix( $prefix ) {
	global $wpdb;

	$prefix = $wpdb->esc_like( '_transient_' . $prefix );
	$sql    = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";
	$keys   = $wpdb->get_results( $wpdb->prepare( $sql, $prefix . '%' ), ARRAY_A );

	if ( is_wp_error( $keys ) ) {
		return [];
	}

	return array_map( function( $key ) {
		// Remove '_transient_' from the option name.
		return ltrim( $key['option_name'], '_transient_' );
	}, $keys );
}

function omitsis_flush_transcients () {
	$prefix_transient = 'omitsis-data-api:';
	delete_transients_with_prefix( $prefix_transient );

	// $option_name = 'omitsis_data_api_cache';
	// $option_value = get_option( $option_name );
	// if ( false !== $option_value ) {
	// 	foreach ( $option_value as $key => $value ) {
	// 		delete_transient( $value );
	// 	}
	// }
	// delete_option( $option_name );
}

function omitsis_data_api_imports () {

	$message = '';

	// Handle file upload here
    if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
		$message = form_submit_file ( 'file_features' );
		$message .= form_submit_file ( 'file_all_data' );
		$message .= form_submit_file ( 'file_countries' );
		// Clear cache
		omitsis_flush_transcients();
    }

	echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
	echo '<p>This is where the data import process from CSV format to the database takes place, ensuring seamless integration of information into our system.</p>';
	?>
	<div class="wrap omitsis_import_admin">
		<div class="omitsis_import_forms">
			<form class="import_form" method="post" enctype="multipart/form-data">
				<h2>Import features for data</h2>
				<input type="file" name="file_features" />
				<input type="submit" value="Import features" />
			</form>

			<form class="import_form" method="post" enctype="multipart/form-data">
				<h2>Import all data</h2>
				<input type="file" name="file_all_data" />
				<input type="submit" value="Import data" />
			</form>

			<form class="import_form" method="post" enctype="multipart/form-data">
				<h2>Import countries data</h2>
				<input type="file" name="file_countries" />
				<input type="submit" value="Import countries" />
			</form>
		</div>

		<?php if ( '' !== $message ) {
			if ( false !== strpos( $message, 'Error' ) ) {
				$class_message = 'omitsis__message_error';
			} else {
				$class_message = 'omitsis__message_success';
			}
			?>
			<div class="omitsis__message <?php echo $class_message; ?>">
				<?php echo $message; ?>
			</div>
		<?php } ?>

	</div>
	<?php
}
