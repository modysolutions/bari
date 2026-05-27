/**
 * Handles the expiration modal behavior, disabling it via AJAX when closed.
 */

class ExpirationModalHandler {
	constructor() {
        this.userId  = expirationModalData.userId;
        this.nonce   = expirationModalData.nonce;
        this.ajaxurl = expirationModalData.ajaxurl;
        this.init();
	}

	init() {
		this.setupEventListeners();
	}

	setupEventListeners() {
		// Wait for the modal to be in the DOM and then configure it.
		this.waitForModal().then(modal => {
			this.setupModalCloseHandler(modal);
		});
	}

	waitForModal() {
		return new Promise((resolve) => {
			const checkModal = () => {
				const modal = document.getElementById('expiration-modal');
				if (modal) {
					resolve(modal);
				} else {
					setTimeout(checkModal, 100);
				}
			};
			checkModal();
		});
	}

	setupModalCloseHandler(modal) {
		// Use the native 'close' event of the dialog element.
		modal.addEventListener('close', () => {
			this.handleExpirationModalClose(modal);
		});
	}

	handleExpirationModalClose(modal) {
		if (this.userId && this.nonce) {
			this.disableExpirationModal(this.userId, this.nonce);
		}
	}

	disableExpirationModal(userId, nonce) {
		fetch(this.ajaxurl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				'action': 'disable_expiration_modal',
				'user_id': userId,
				'nonce': nonce
			})
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				console.log('Expiration modal successfully disabled.');
			} else {
				console.error('Error disabling the modal:', data.data);
			}
		})
		.catch(error => {
			console.error('Request error:', error);
		});
	}
}

// Inicialize the handler.
document.addEventListener('DOMContentLoaded', () => {
	if (document.getElementById('expiration-modal')) {
		window.expirationModalHandler = new ExpirationModalHandler();
	}
});