<?php

/**
 * Plugin name: Tamarind Intelligence - Import users extension
 * Description: An extension of Import and export users and customers.
 * Author: Tamarind Intelligence
 * Plugin URI: https://tamarindintelligence.com
 * Domain: tamarind-import-users-extension
 */
// @todo: add namespace to plugin.
defined( 'ABSPATH' ) || exit;

add_filter('acui_send_email_for_user', '__return_true');
add_filter('acui_import_email_body', 'tamarind_acui_send_email_for_user', 10, 5);

add_action('post_acui_import_single_user', 'tamarind_post_acui_import_single_user', 10, 3);

function tamarind_acui_send_email_for_user($body, $headers, $data, $created, $user_id) : string {
	$user = get_user($user_id);
	if(!$user) return $body;
	$keys = [ '{{first_name}}' ];
	$values = [ ucwords($user->first_name) ];
	return str_replace( $keys, $values , $body );
}


function tamarind_post_acui_import_single_user($headers, $data, $user_id): void {

}
