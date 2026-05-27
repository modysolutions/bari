<?php
/**
 * Modals Form Gravity Forms for Tamarind Forms.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\modals_form;

defined( 'ABSPATH' ) || exit;

/**
 * Display modal register on exit
 *
 * @return string
 */
function display_modal_register_onexit() {
	if ( ! is_admin() && ! is_user_logged_in() ) {
		$time_exit_popup = get_field( 'cookie_register_onexit', 'options' ) ?? 1;

		ob_start();
		?>
		<dialog id="bio_ep" class="tm-modal">
			<div class="tm-modal-content">
				<button class="tm-modal-close" type="button">×</button>
				<div class="modal-content">
					<?php
					if ( is_plugin_active( 'tamarind-forms/tamarind-forms.php' ) ) {
						\tamarind_forms\display_form\display_form( 'register_onexit', true, get_the_id() );
					}
					?>
				</div>
			</div>
		</dialog>

		<script>
			/**
			 * Get cookie by name
			 * @param {string} name
			 * @returns {string|null}
			 */
			function getCookie(name) {
				const value = `; ${document.cookie}`;
				const parts = value.split(`; ${name}=`);
				if (parts.length === 2) return parts.pop().split(';').shift();
				return null;
			}

			/**
			 * Set cookie
			 * @param {string} name
			 * @param {boolean} value
			 * @returns {void}
			 */
			function setCookie(name, value) {
				const expiry = new Date();
				expiry.setTime(expiry.getTime() + 24 * 60 * 60 * 1000 * <?php echo $time_exit_popup; ?>);
				const booleanValue = value ? "true" : "false";
				document.cookie = `${name}=${booleanValue}; expires=${expiry.toUTCString()}; path=/`;
			}

			document.addEventListener("DOMContentLoaded", () => {
				const bio_ep = document.getElementById('bio_ep');
				
				if (!bio_ep) return;

				/**
				 * Show popup on exit intent from any edge
				 * @returns {void}
				 */
				document.addEventListener('mouseleave', function(e) {
					// Check if mouse is leaving the viewport from any edge
					const isLeavingTop = e.clientY < 10;
					const isLeavingBottom = e.clientY > (window.innerHeight - 10);
					const isLeavingLeft = e.clientX < 10;
					const isLeavingRight = e.clientX > (window.innerWidth - 10);
					
					if (isLeavingTop || isLeavingBottom || isLeavingLeft || isLeavingRight) {
						const hiddenPopUp = getCookie("tamarind_hidden_onexitpopup");
						if (!hiddenPopUp || hiddenPopUp === "false") {
							// Use TMModal to open the modal
							if (window.tmModal) {
								window.tmModal.openModalById('#bio_ep');
								setCookie("tamarind_hidden_onexitpopup", true);
							}
						}
					}
				});

			});
		</script>
		<?php
		return ob_get_clean();
	}

	return '';
}
