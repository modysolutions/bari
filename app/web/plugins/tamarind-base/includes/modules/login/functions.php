<?php

namespace tamarind_base\login;

defined('ABSPATH') || exit;

define('TM_LOGIN_OPTIONS_PAGE_SLUG', 'tm-login-option');
define('TM_LOGIN_MODULE_PATH', __DIR__);

add_action('acf/options_page/save', __NAMESPACE__.'\\acf_options_page_save');
add_action('template_redirect', __NAMESPACE__.'\\template_redirect');
add_action('login_enqueue_scripts', __NAMESPACE__.'\\login_enqueue_scripts', 1000);
add_action('login_head', __NAMESPACE__.'\\login_head');

add_filter( 'lostpassword_url', function( $lostpassword_url, $redirect ) {
    if ( function_exists( 'get_field' ) && get_field( 'tm_login_customize_login_page', 'option' ) ) {
        $default_url = network_site_url( 'wp-login.php?action=lostpassword', 'login' );
        if ( ! empty( $redirect ) ) {
            $default_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $default_url );
        }
        return $default_url;
    }

    return $lostpassword_url;
}, 99, 2 );

function acf_options_page_save(string|int $post_id, string|null $menu_slug = null): void {
    if ($post_id === 'options' &&
        (sanitize_text_field($_GET['page']) === TM_LOGIN_OPTIONS_PAGE_SLUG ||
            $menu_slug === TM_LOGIN_OPTIONS_PAGE_SLUG)) {
        _generate_vars_file();
    }
}

function template_redirect(): void {
    $force_auth = get_field('tm_login_authenticated_users_only', 'option');

    if ($force_auth && !is_user_logged_in()) {
        if (!is_login() && !(defined('DOING_AJAX') && DOING_AJAX)) {
            wp_safe_redirect(wp_login_url(get_permalink()));
            exit;
        }
    }
}

function login_enqueue_scripts(): void {
    if (!function_exists('get_field')) {
        return;
    }
    $customize_login_page = get_field('tm_login_customize_login_page', 'option');
    if (!$customize_login_page) {
        return;
    }

    $vars_file = TM_PLUGIN_DIR_PATH.'assets/css/login-vars.css';
    if (!file_exists($vars_file) || filesize($vars_file) === 0) {
        _generate_vars_file();
    }

    $version = file_exists($vars_file) ? filemtime($vars_file) : TM_BASE_VERSION;
    $font_css = get_stylesheet_directory().'/css/google-fonts-bitter.css';
    if (file_exists($font_css)) {
        wp_enqueue_style('tm-login-fonts', get_stylesheet_directory_uri().'/css/google-fonts-bitter.css', null, $version);
    }

    if (file_exists($vars_file) && filesize($vars_file) > 0) {
        wp_register_style('tm-login-vars', TM_PLUGIN_DIR_URL.'assets/css/login-vars.css', null, $version);
        wp_enqueue_style('tm-login-vars');
    } else {
        add_action('login_head', __NAMESPACE__.'\\inline_login_vars', 1);
    }

    wp_register_style('tm-login-styles', TM_PLUGIN_DIR_URL.'assets/css/login.css', null, TM_BASE_VERSION);
    wp_enqueue_style('tm-login-styles');
}

function _generate_vars_file(): void {
    $styles = _build_vars();
    _save_vars_file($styles);
}

function _build_vars(): array {
    $styles = _save_styles();
    $styles = _save_notices_colors($styles);
    $styles = _save_form_styles($styles);
    $styles = _save_layout($styles);

    if (empty($styles['--tm_login_font_family'])) {
        $styles['--tm_login_font_family'] = "'Bitter', georgia, serif";
    }

    return $styles;
}

function inline_login_vars(): void {
    $styles = _build_vars();
    if (empty($styles)) {
        return;
    }
    echo "<style id=\"tm-login-vars-inline\">\nbody.login {\n";
    foreach ($styles as $key => $value) {
        if ($key === 'import') {
            continue;
        }
        $safe_key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        $safe_value = str_replace(array('<', '>', '&'), '', $value);
        echo "\t".$safe_key.': '.$safe_value.";\n";
    }
    echo "}\n</style>\n";
}

function login_head(): void {
    if (!function_exists('get_field') || !get_field('tm_login_customize_labels', 'option')) {
        return;
    }

    $labels = array(
        'user' => get_field('tm_login_label_username', 'option'),
        'pass' => get_field('tm_login_label_password', 'option'),
        'lost' => get_field('tm_login_label_lost_password', 'option'),
        'submit' => get_field('tm_login_label_submit', 'option'),
        'reset' => get_field('tm_login_label_reset_password', 'option'),
    );

    $placeholders = array(
        'user' => get_field('tm_login_placeholder_username', 'option'),
        'pass' => get_field('tm_login_placeholder_password', 'option'),
    );

    $lost_password_position = get_field('tm_login_lost_password_position', 'option');
    $registration_text = get_field('tm_login_registration_text', 'option');

    ?>
  <script type="text/javascript">
      document.addEventListener('DOMContentLoaded', function () {
          const loginLabel = document.querySelector('label[for="user_login"]');
          const loginField = document.getElementById('user_login');
          const passwordLabel = document.querySelector('label[for="user_pass"]');
          const passwordField = document.getElementById('user_pass');
          const lostPasswordLabel = document.querySelector('.wp-login-lost-password');
          const submitButton = document.querySelector('#wp-submit');

          if (loginLabel) {
              loginLabel.childNodes[0].nodeValue = '<?php echo esc_js($labels['user']); ?>';
              loginField.setAttribute('placeholder', '<?php echo esc_js($placeholders['user']); ?>');
          }

          if (passwordLabel) {
              passwordLabel.childNodes[0].nodeValue = '<?php echo esc_js($labels['pass']); ?>';
              passwordField.setAttribute('placeholder', '<?php echo esc_js($placeholders['pass']); ?>');
          }

          if (lostPasswordLabel) {
              lostPasswordLabel.textContent = '<?php echo esc_js($labels['lost']); ?>';
              <?php if($lost_password_position === 'before_submit'): ?>
                const forgetMeNot = document.querySelector('.forgetmenot');
                if (forgetMeNot) {
                    const pipe = document.createTextNode(' | ');
                    forgetMeNot.appendChild(pipe);
                    forgetMeNot.insertAdjacentElement('beforeend', lostPasswordLabel);
                }
              <?php endif;?>
          }

          if (submitButton) {
              submitButton.value = '<?php echo esc_js($labels['submit']); ?>';
          }

          <?php if ($registration_text): ?>
          const navElement = document.querySelector('#nav');
          if (navElement) {
              const registrationText = <?php echo json_encode($registration_text); ?>;
              const registrationDiv = document.createElement('div');
              registrationDiv.innerHTML = registrationText;
              navElement.appendChild(registrationDiv);
          }
          <?php endif;?>
      });
  </script>
    <?php
}

function get_logo_url(): ?string {
    if (get_field('tm_login_use_custom_logo', 'option')) {
        $logo_url = get_field('tm_login_custom_logo', 'option');
        $has_custom_logo = !!$logo_url;

        if ($has_custom_logo) {
            return esc_url($logo_url);
        }

        return null;
    }

    return null;
}

function _save_styles(): array {
    if (!get_field('tm_login_customize_login_page', 'option')) {
        return array();
    }
    $styles = array();
    $colors = array(
        'tm_login_notice_success',
        'tm_login_notice_error',
        'tm_login_notice_warning',
        'tm_login_notice_info',
        'tm_login_form_background_color',
        'tm_login_border_color',
        'tm_login_form_field_border_color',
        'tm_login_submit_button_bg_color',
        'tm_login_submit_button_text_color',
        'tm_login_default_button_background_color',
        'tm_login_default_button_text_color',
        'tm_login_background_color',
        'tm_login_text_color',
        'tm_login_link_color',
        'tm_login_container_background_color',
    );
    foreach ($colors as $color) {
        $styles["--{$color}"] = get_field($color, 'option');
    }

    $custom_logo = get_logo_url();
    if ($custom_logo) {
        $styles['--tm_login_custom_logo'] = "url(\"{$custom_logo}\")";
    }

    $body_background_image = get_field('tm_login_body_background_image', 'option');
    if (!empty($body_background_image)) {
        $styles['--tm_login_body_background_image'] = "url(\"{$body_background_image}\")";
        $styles['--tm_login_background_position'] = get_field('tm_login_background_position', 'option');
        $styles['--tm_login_background_repeat'] = get_field('tm_login_background_repeat', 'option');
        $styles['--tm_login_background_size'] = get_field('tm_login_background_size', 'option');
    }

    if (get_field('tm_login_use_custom_logo', 'option')) {
        $styles["--tm_login_logo_width"] =
            get_field('tm_login_logo_width', 'option').get_field('tm_login_logo_width_unit', 'option');

        $styles["--tm_login_logo_height"] =
            get_field('tm_login_logo_height', 'option').get_field('tm_login_logo_height_unit', 'option');
    }

    $styles["--tm-login--login_container_width"] =
        get_field('tm_login_container_width', 'option').get_field('tm_login_container_width_unit', 'option');
    $logo_height = get_field('tm_login_logo_height', 'option');
    if ($logo_height === 'auto') {
        $styles["--tm_login_logo_height"] = $logo_height;
    }

    $styles['--tm_login_show_back_to_blog'] =
        get_field('tm_login_show_back_to_blog', 'option') === 1 ? 'block' : 'none';
    $styles['--tm_show_privacy_policy_page_link'] =
        get_field('tm_show_privacy_policy_page_link', 'option') === 1 ? 'block' : 'none';

    $styles['--tm_login_username_show_label'] = get_field('tm_login_username_show_label', 'option') ?
        'inline-block' : 'none';
    $styles['--tm_login_password_show_label'] = get_field('tm_login_password_show_label', 'option') ?
        'inline-block' : 'none';
    $styles['--tm_login_lost_password_position'] = get_field('tm_login_lost_password_position', 'option') === 'before_submit' ?
        'none' : 'inline-block';

    return $styles;
}

function _save_notices_colors(array $styles): array {
    $styles['--tm_login_notice_success'] = get_field('tm_login_notice_success', 'option');
    $styles['--tm_login_notice_error'] = get_field('tm_login_notice_error', 'option');
    $styles['--tm_login_notice_warning'] = get_field('tm_login_notice_warning', 'option');
    $styles['--tm_login_notice_info'] = get_field('tm_login_notice_info', 'option');

    return $styles;
}

function _save_form_styles(array $styles): array {
    $styles['--tm_login_form_width'] =
        get_field('tm_login_form_width', 'option').get_field('tm_login_form_width_unit', 'option');
    $styles['--tm_login_border_color'] = get_field('tm_login_border_color', 'option');
    $styles['--tm_login_border_width'] =
        get_field('tm_login_border_width', 'option').get_field('tm_login_border_width_unit', 'option');
    $styles['--tm_login_form_background_color'] = get_field('tm_login_form_background_color', 'option');
    $styles['--tm_login_form_field_border_color'] = get_field('tm_login_form_field_border_color', 'option');
    $styles['--tm_login_form_padding'] =
        get_field('tm_login_form_padding', 'option').get_field('tm_login_form_padding_unit', 'option');
    $styles['--tm_login_form_border_radius'] =
        get_field('tm_login_form_border_radius', 'option').get_field('tm_login_form_border_radius_unit', 'option');
    $styles['--tm_login_form_field_border'] =
        get_field('tm_login_form_field_border', 'option').get_field('tm_login_form_field_border_unit', 'option');
    $styles['--tm_login_form_field_border_radius'] = get_field('tm_login_form_field_border_radius', 'option').
        get_field('tm_login_form_field_border_radius_unit', 'option');

    return $styles;
}

function _save_layout(array $styles): array {
    $layout = get_field('tm_login_layout', 'option');
    if (!$layout) {
        return $styles;
    }

    $styles['--tm_login_align_items'] = 'center';
    $styles['--tm_login_justify_content'] = 'auto';
    $styles['--tm_login_flex_direction'] = 'auto';
    $styles['--tm_login_login_container_display'] = 'initial';
    $styles['--tm_login_login_container_align_items'] = 'initial';
    $styles['--tm_login_login_container_direction'] = 'initial';
    $styles['--tm_login_login_container_justify_content'] = 'initial';

    if ($layout !== 'center') {
        $styles['--tm_login_align_items'] = 'stretch';
        $styles['--tm_login_justify_content'] = 'flex-start';
        $styles['--tm_login_flex_direction'] = 'row';
        $styles['--tm_login_login_container_padding'] = '1rem';
        $styles['--tm_login_login_container_margin'] = 0;
        $styles['--tm_login_login_container_display'] = 'flex';
        $styles['--tm_login_login_container_align_items'] = 'center';
        $styles['--tm_login_login_container_direction'] = 'column';
        $styles['--tm_login_login_container_justify_content'] = 'center';
    } else {
        $styles['--tm_login_login_container_padding'] = '5% 0 0 0';
        $styles['--tm_login_login_container_margin'] = 'auto';
    }

    if ($layout === 'right') {
        $styles['--tm_login_justify_content'] = 'flex-end';
    }

    return $styles;
}

function _save_vars_file($styles): void {
    if (count($styles) > 0) {
        $file = TM_PLUGIN_DIR_PATH.'assets/css/login-vars.css';
        if (!file_exists($file)) {
            if (!is_dir(TM_PLUGIN_DIR_PATH.'assets/css')) {
                mkdir(TM_PLUGIN_DIR_PATH.'assets/css', 0755, true);
            }
            touch($file);
        }
        $lines = "body.login {\n";
        foreach ($styles as $key => $value) {
            if ($key === 'import') {
                continue;
            }
            if (str_contains($key, 'url')) {
                $value = "\"{$value}\"";
            }
            $lines .= "\t{$key}: {$value};\n";
        }
        $lines .= "}\n";
        file_put_contents($file, $lines);
    }
}
