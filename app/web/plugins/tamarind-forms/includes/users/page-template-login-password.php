<?php
/*
Template Name: Login and Lost password Page
*/

// Detectar qué formulario está mostrando
$login_page = get_field('user_label_login_page', 'option');
$forgot_page = get_field('user_label_forgot_page', 'option');
$reset_page = get_field('user_label_reset_page', 'option');

if ($login_page == get_the_permalink()) {
	$show_login = true;
} elseif ($forgot_page == get_the_permalink()) {
	$show_forgot = true;
} elseif ($reset_page == get_the_permalink()) {
	$show_reset = true;
} else {
	$show_login = false;
	$show_forgot = false;
	$show_reset = false;
}

// IF USER ARRIVES TO THIS PAGE PREVIOUSLY LOGGED IN WILL BE REDIRECTED TO USER AREA
if (is_user_logged_in() && $show_login) {
	wp_redirect(get_bloginfo('url') . '/user-area/');
	exit;
}

get_header(); ?>

<div class="super_title">
	<div class="wrap">
		<h1><?php the_title(); ?></h1>
	</div>
</div>
<div id="main-content" class="content-sidebar-wrap">
	<div class="wrap">



		<?php while (have_posts()) : the_post(); ?>
			<div class="content">
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>



					<div class="entry-content">
						<?php
						the_content();

						if (is_plugin_active('tamarind-forms/tamarind-forms.php')) {

							if ( $show_login ) {

								//\tamarind_forms\display_form\display_form( 'login_user', true, get_the_id() ); ?>

								<div class="gform_wrapper gform-theme gform-theme--foundation gform-theme--framework gform-theme--orbital tm-form_wrapper tm-form-style-default_wrapper">
									<?php wp_login_form(); ?>
								</div>

								<p class="wp-login-links">
									<a rel="nofollow" class="wp-login-register" href="<?php echo get_field('user_label_register_page', 'option') ?>">Register</a> | <a class="wp-login-lost-password" href="<?php echo get_field('user_label_forgot_page', 'option') ?>">Lost your password?</a>
								</p>
								<?php

							} elseif ( $show_forgot ) {

								\tamarind_forms\display_form\display_form( 'forgot_password', true, get_the_id() );

							} elseif ($show_reset) {

								// Comprobar si los valores $_GET['key'] y $_GET['login'] existen
								if (isset($_GET['key']) && isset($_GET['login'])) {
									$key = sanitize_text_field($_GET['key']);
									$login = sanitize_text_field($_GET['login']);
									$validation = check_password_reset_key($key, $login);

									// Si la clave es inválida, mostrar mensaje de error y no mostrar el formulario
									if (is_wp_error($validation)) {
										echo '<div class="gform_confirmation_wrapper">Error: Your password reset link appears to be invalid. <br>Please request a new link below.</div>';
										$show_reset = false;
									} else {
									\tamarind_forms\display_form\display_form( 'reset_password', true, get_the_id() );
									}
								} else {
									echo '<div class="gform_confirmation_wrapper">Error: Your password reset link appears to be invalid. <br>Please request a new link below.</div>';
								}
							}

						} ?>

					</div> <!-- .entry-content -->


				</article> <!-- .et_pb_post -->

			<?php endwhile; ?>


			</div> <!-- #left-area -->


	</div>
</div> <!-- #main-content -->

<?php get_footer(); ?>
