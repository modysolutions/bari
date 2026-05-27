<?php

/**
 * Handle common Functions for admin
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\data_view;

global $wpdb;

define( 'PREFIX_TABLE_PLUGIN_VIEW', $wpdb->prefix . 'omitsis_data_api_' );
defined( 'ABSPATH' ) || exit;

$default_tab = null;
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
switch($tab) :
	case 'region':
		define( 'TABLE_FIELD_NAME_VIEW','name' );
		break;
	case 'values':
		define( 'TABLE_FIELD_NAME_VIEW','slug_feature_name' );
		break;
	case 'features':
		define( 'TABLE_FIELD_NAME_VIEW','slug_feature_name' );
		break;
	default:
		define( 'TABLE_FIELD_NAME_VIEW','name' );
		break;
endswitch;


/* This file contains the class definition for the `WP_List_Table` class, which is a base class for creating tables in WordPress
	admin screens. By including this file, the code is making sure that the `WP_List_Table` class is available for use. */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Omitis_Data_API_WP_List_Table extends \WP_List_Table {

    private $table_name;
	private $table_data;

    public function __construct($table_name) {
        parent::__construct(array(
            'singular' => 'item',
            'plural'   => 'items',
            'ajax'     => false
        ));
        $this->table_name = $table_name;
    }

	function column_default($item, $column_name) {
        return $item[$column_name];
    }

    public function get_columns() {

		global $wpdb;

		// get fields name from table
		$fields = $wpdb->get_results( "DESCRIBE $this->table_name" );

		// [0] => stdClass Object
        // (
        //     [Field] => id
        //     [Type] => bigint(20) unsigned
        //     [Null] => NO
        //     [Key] => PRI
        //     [Default] =>
        //     [Extra] => auto_increment
        // )

		$columns = array();
		foreach ($fields as $item) {
			$columns[$item->Field] = __($item->Field, 'omitsis-data-api');
		}

        return $columns;
    }

	function get_sortable_columns() {
		global $wpdb;

		// get fields name from table
		$fields = $wpdb->get_results( "DESCRIBE $this->table_name" );

		$sortable_columns = array();
		foreach ($fields as $item) {
			$sortable_columns[$item->Field] = array($item->Field, true);
		}

        return $sortable_columns;
    }

	public function get_table_data ( $search = '' ) {
		global $wpdb;

		$select = "SELECT * FROM $this->table_name";
		$where  = '';
		if ( !empty($search) ) {
			$where = " WHERE ";
			$fields = $wpdb->get_results( "DESCRIBE $this->table_name" );
			foreach ($fields as $item) {
				if ( $item !== reset($fields) ) {
					$where .= "OR ";
				}
				$where .= "{$item->Field} Like '%{$search}%' ";
			}
        }
		$sql = $select . $where;
        return $wpdb->get_results(
            $sql,
            ARRAY_A
        );
	}

	function usort_reorder($a, $b) {
        // If no sort, default to user_login
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : TABLE_FIELD_NAME_VIEW;

        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';

        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    // Aquí puedes agregar lógica para recuperar los datos de tu tabla
    public function prepare_items() {

		//data
		if ( isset($_POST['s']) ) {
            $this->table_data = $this->get_table_data($_POST['s']);
        } else {
            $this->table_data = $this->get_table_data();
        }

		$columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

		// here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

		usort($this->table_data, array(&$this, 'usort_reorder'));

		/* pagination */
        $per_page = 100;
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

		$this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

		$this->set_pagination_args(array(
			'total_items' => $total_items, // total number of items
			'per_page'    => $per_page, // items to show on a page
			'total_pages' => ceil( $total_items / $per_page ) // use ceil to round up
		));

		$this->items = $this->table_data;
    }
}

function omitsis_display_table_data ( $table_name ) {
	$omitsis_table = new Omitis_Data_API_WP_List_Table($table_name);
	$omitsis_table->prepare_items();
	?>
	<div class="wrap">
		<h1><b>Database Table:</b> <?php echo $table_name; ?></h1>
		<form id="<?php echo $table_name; ?>-table" method="POST">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
			<?php
				$omitsis_table->search_box('search', 'search_id');
				$omitsis_table->display();
			 ?>
		</form>
	</div>
	<hr />
	<?php
}

function omitsis_data_api_data_views () {

	echo '<h1>View data</h1>';

	//Get the active tab from the $_GET param
	$default_tab = null;
	$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

	?>
	<nav class="nav-tab-wrapper">
		<a href="?page=data-tamarind-api-view-data" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Country</a>
		<a href="?page=data-tamarind-api-view-data&tab=region" class="nav-tab <?php if($tab==='region'):?>nav-tab-active<?php endif; ?>">Region</a>
		<a href="?page=data-tamarind-api-view-data&tab=values" class="nav-tab <?php if($tab==='values'):?>nav-tab-active<?php endif; ?>">Values</a>
		<a href="?page=data-tamarind-api-view-data&tab=features" class="nav-tab <?php if($tab==='features'):?>nav-tab-active<?php endif; ?>">Features</a>
    </nav>

	<div class="tab-content">
		<?php
		switch($tab) :
			case 'region':
				$table_name = PREFIX_TABLE_PLUGIN_VIEW . 'region';
				break;
			case 'values':
				$table_name = PREFIX_TABLE_PLUGIN_VIEW . 'meta_value';
				break;
			case 'features':
				$table_name = PREFIX_TABLE_PLUGIN_VIEW . 'features';

				break;
			default:
				$table_name = PREFIX_TABLE_PLUGIN_VIEW . 'country';

				break;
		endswitch;
		omitsis_display_table_data( $table_name );
		?>
    </div>

	<?php
}
