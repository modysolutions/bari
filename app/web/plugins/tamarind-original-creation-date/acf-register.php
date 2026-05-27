<?php
/**
 * Registers the ACF fields group for original creation date.
 *
 * @package Tamarind_Original_Creation_Date
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_original_creation_date;

/**
 * Registers the ACF fields group for original creation date
 */
if ( function_exists( 'acf_add_local_field_group' ) ) {
    add_action( 'acf/init', __NAMESPACE__ . '\register_acf_fields' );
}

/**
 * Registers the ACF fields group for original creation date
 */
function register_acf_fields() 
{

    $field_original_creation_date = array(
        'key'            => 'field_original_creation_date',
        'label'          => 'Original Creation Date',
        'name'           => 'original_creation_date',
        'type'           => 'date_picker',
        'display_format' => 'd/m/Y',
        'return_format'  => 'd/m/Y',
        'first_day'      => 1,
    );
    
    acf_add_local_field_group(array(
        'key'        => 'group_original_creation_date',
        'title'      => 'Original Creation Date',
        'fields'     => array(
            $field_original_creation_date,
        ),
        'location'   => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'post',
                ),
            ),
        ),
        'menu_order' => 1,
        'position'   => 'side',
        'style'      => 'default',
    ));
}
