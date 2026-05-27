<?php

/**
 * User Reset Password.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\users\password;

defined('ABSPATH') || exit;

/* 
 * Forgot Password Form: Validar campos formulario
 */
add_filter('gform_field_validation', __NAMESPACE__ . '\forgot_password_validate_field', 10, 4);

function forgot_password_validate_field($result, $value, $form, $field)
{

    // Aplicar validación solo si el campo hidden 'type-form' es 'forgot-password'
    if ($field->type == 'email' && \tamarind_forms\display_form\get_type_form($form) == 'forgot-password') {

        $User = get_user_by('email', $value);
        if (!$User->ID && $result['is_valid'] === true) {
            $result['is_valid'] = false;
            $result['message'] = 'That email address does not exist in our system.';
        }
    }
     
    return $result;
}

/*
* Forgot Password Form: Enviar notificación después de enviar el formulario validado
*/
add_filter('gform_notification', __NAMESPACE__ . '\forgot_password_notification', 10, 3);

function forgot_password_notification($notification, $form, $entry)
{

    // Aplicar validación solo si el campo hidden 'type-form' es 'forgot-password'
    if (\tamarind_forms\display_form\get_type_form($form) == 'forgot-password') {

        $reset_email = '';

        // Buscar el campo email por su clase
        foreach ($form['fields'] as $field) {
            if (strpos($field->cssClass, 'reset-email') !== false) {
                $reset_email = rgar($entry, $field->id);
                break;
            }
        }

        // Send the forgot password email
        $user = get_user_by('email', $reset_email);

        if ($user->ID) {
            $displayName = $user->display_name;
            $reset_link = add_query_arg([
                'key' => get_password_reset_key($user),
                'action' => 'rp',
                'login' => urlencode($user->user_login)
            ], get_field('user_label_reset_page', 'option'));

            $notification['message'] = str_replace('{full_name}', $displayName, $notification['message']);
            $notification['message'] = str_replace('{password_link}', $reset_link, $notification['message']);
        }
    }

    return $notification;
}


/* 
 * Reset Password Form: comprueba que check_password_reset_key() es válido al cargar la página
 */
add_filter('gform_get_form_filter', __NAMESPACE__ . '\reset_password_conditional_message', 10, 2);

function reset_password_conditional_message($form_string, $form)
{
    
        // Comprobar si los valores $_GET['key'] y $_GET['login'] existen
        if (isset($_GET['key']) && isset($_GET['login'])) {
            $key = sanitize_text_field($_GET['key']);
            $login = sanitize_text_field($_GET['login']);
            $validation = check_password_reset_key($key, $login);

            // Si la clave es inválida, agregar mensaje de error solo en esta carga
            if (is_wp_error($validation)) {
                $error_message = '<div class="validation-error">Error: Your password reset link appears to be invalid. Please request a new link below.</div>';
                $form_string = $error_message . $form_string;
            }
        }
    

    return $form_string;
}


/* 
 * Reset Password Form: valida check_password_reset_key() a nivel de campo. 
 */
add_filter('gform_validation', __NAMESPACE__ . '\reset_password_validate_key');

function reset_password_validate_key($validation_result)
{
    // Obtener el formulario desde $validation_result
    $form = $validation_result['form'];

    // Comprobar si los valores $_GET['key'] y $_GET['login'] existen
    if (isset($_GET['key']) && isset($_GET['login'])) {
        $key = sanitize_text_field($_GET['key']);
        $login = sanitize_text_field($_GET['login']);
        $validation = check_password_reset_key($key, $login);

        // Si la clave es inválida, establecer is_valid como false y mostrar el mensaje
        if (is_wp_error($validation)) {
            $validation_result['is_valid'] = false;
            // Establecer el mensaje de error en el primer campo del formulario
            $form['fields'][0]->validation_message = 'Error: Your password reset link appears to be invalid. Please request a new link below.';
            $form['fields'][0]->failed_validation = true;
        }
    }

    // Asignar el formulario modificado al resultado de la validación
    $validation_result['form'] = $form;

    return $validation_result;
}


/* 
 * Reset Password Form: Validación exitosa, cambiemos la contraseña
 */
add_filter('gform_after_submission', __NAMESPACE__ . '\reset_password_update_password', 10, 2);

function reset_password_update_password($entry, $form) {

    // Obtener el valor de type-form
    $type_form_value = null;
    foreach ($form['fields'] as $f) {
        if ($f->type == 'hidden' && $f->name == 'type-form') {
            // Usar el valor de la entrada en lugar del valor predeterminado (get_type_form no funciona en este caso)
            $field_id = $f->id;
            $type_form_value = rgar($entry, $field_id);
            break;
        }
    }    

    // Aplicar validación solo si el campo hidden 'type-form' es 'reset-password'
    if ($type_form_value == 'reset-password') {

        $login = sanitize_text_field($_GET['login']);
        $user = get_user_by('login', $login);
            
        if ($user) {
            $new_password = '';
            foreach ($form['fields'] as $field) {
                if (strpos($field->cssClass, 'new-password') !== false) {
                    $new_password = rgar($entry, $field->id);
                    break;
                }
            }

            if (!empty($new_password)) {
                wp_set_password($new_password, $user->ID);
                error_log('Contraseña actualizada para el usuario: ' . $user->ID);
            } else {
                error_log('No se encontró la nueva contraseña.');
            }
        } else {
            error_log('Usuario no encontrado.');
        }
    }
}

/**
 * Change Password Form: Validar Current Password
 */
add_filter('gform_validation', __NAMESPACE__ . '\change_password_validation');

function change_password_validation($validation_result)
{
    $form = $validation_result['form'];
    $entry = \GFFormsModel::get_current_lead(); // Obtener los datos del envío del formulario.

    // Comprobar si el type-form es 'change-password'
    $type_form_value = null;
    foreach ($form['fields'] as $field) {
        if ($field->type == 'hidden' && $field->name == 'type-form') {
            $field_id = $field->id;
            $type_form_value = rgar($entry, $field_id);
            break;
        }
    }

    if ($type_form_value !== 'change-password') {
        return $validation_result;
    }

    $current_password = '';
    foreach ($form['fields'] as $field) {
        $value = rgar($entry, $field->id);
        if (strpos($field->cssClass, 'current-password') !== false) {
            $current_password = $value;
        }
    }

    $current_user = wp_get_current_user();

    // Validar que la contraseña actual es correcta
    if (!wp_check_password($current_password, $current_user->user_pass, $current_user->ID)) {
        foreach ($form['fields'] as &$field) {
            if (strpos($field->cssClass, 'current-password') !== false) {
                $field->failed_validation = true;
                $field->validation_message = 'Your current password is incorrect.';
                $validation_result['is_valid'] = false;
                break;
            }
        }
    }

    $validation_result['form'] = $form;

    return $validation_result;
}

/**
 * Change Password Form: Cambiar la contraseña después de la validación exitosa
 */
add_action('gform_after_submission', __NAMESPACE__ . '\change_password_update_password', 10, 2);

function change_password_update_password($entry, $form)
{
    // Comprobar si el type-form es 'change-password'
    $type_form_value = null;
    foreach ($form['fields'] as $field) {
        if ($field->type == 'hidden' && $field->name == 'type-form') {
            $field_id = $field->id;
            $type_form_value = rgar($entry, $field_id);
            break;
        }
    }

    if ($type_form_value !== 'change-password') {
        return;
    }    

    $new_password = '';
    foreach ($form['fields'] as $field) {
        if (strpos($field->cssClass, 'new-password') !== false) {
            $new_password = rgar($entry, $field->id);
            break;
        }
    }

    if (!empty($new_password)) {
        $current_user = wp_get_current_user();
        wp_set_password($new_password, $current_user->ID);
        wp_set_auth_cookie($current_user->ID); // Mantener al usuario logeado después de cambiar la contraseña
        error_log('Contraseña cambiada para el usuario: ' . $current_user->ID);
    } else {
        error_log('No se encontró la nueva contraseña.');
    }
}
