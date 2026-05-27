// Updates the hidden field with the country code value from the phone field.
document.addEventListener('DOMContentLoaded', function () {
	var forms = document.querySelectorAll('.tm-form');

	forms.forEach(function (form) {
		var phoneContainer = form.querySelector('.tm-phone');
		var hiddenContainer = form.querySelector('.tm-hidden-country_code');

		if (phoneContainer && hiddenContainer) {
			var hiddenField = hiddenContainer.querySelector('input[type="hidden"]');
			var phoneField = phoneContainer.querySelector('input[type="tel"]');

			// Function to set the country code in the hidden field
			function updateHiddenCountryCode() {
				var dialCodeElement = phoneContainer.querySelector('.iti__selected-dial-code');
				if (dialCodeElement) {
					var dialCode = dialCodeElement.textContent.trim();
					hiddenField.value = dialCode;
				}
			}

			// Function to set the country code in the dropdown when the form loads
			function setInitialCountryCode() {
				// Wait until the dropdown menu is available
				var intervalId = setInterval(function () {
					var selectedFlag = phoneContainer.querySelector('.iti__selected-flag'); // Selected flag button

					if (selectedFlag) {
						clearInterval(intervalId); // Stop the interval when the list is found

						var initialCode = hiddenField.value.trim();
						if (initialCode.startsWith('+')) {
							initialCode = initialCode.substring(1); // Remove an additional "+" if present
						}

						var dialCodeElement = phoneContainer.querySelector('.iti__selected-dial-code');
						if (dialCodeElement) {
							dialCodeElement.textContent = '+' + initialCode;
						}

					}
				}, 100); 
			}

			// Observer to dynamically detect changes in the dropdown
			var observer = new MutationObserver(function (mutationsList) {
				mutationsList.forEach(function (mutation) {
					if (
						mutation.type === 'childList' &&
						mutation.target.classList.contains('iti__selected-dial-code')
					) {
						updateHiddenCountryCode();
					}
				});
			});

			// Observe changes in the phone container
			observer.observe(phoneContainer, { childList: true, subtree: true });

			// Set the initial code when the form loads
			if (phoneField && phoneField.value.trim() !== '') {
				setInitialCountryCode();
			}

			// Listener for manual changes to the country code (selection in the dropdown)
			phoneField.addEventListener('countrychange', updateHiddenCountryCode);
		}
	});
});


// Rating article form
document.addEventListener('DOMContentLoaded', function () {
	const stars = document.querySelectorAll('.rating-container .gfield_radio .gchoice input');

	stars.forEach((input, index) => {
		const star = input.parentElement;

		// Add hover effect
		star.addEventListener('mouseover', () => {
			clearSelection();
			highlightStars(index);
		});

		// Remove hover effect
		star.addEventListener('mouseout', () => {
			clearSelection();
			setSelectedStars();
		});

		// Add click effect
		star.addEventListener('click', () => {
			selectStars(index);
			input.checked = true;
		});
	});

	function highlightStars(index) {
		for (let i = 0; i <= index; i++) {
			stars[i].parentElement.classList.add('selected');
		}
	}

	function clearSelection() {
		stars.forEach(star => {
			star.parentElement.classList.remove('selected');
		});
	}

	function selectStars(index) {
		// Clear previous selection
		stars.forEach(star => {
			star.parentElement.classList.remove('permanent-selected');
		});

		// Set new selection
		for (let i = 0; i <= index; i++) {
			stars[i].parentElement.classList.add('permanent-selected');
		}
	}

	function setSelectedStars() {
		stars.forEach(star => {
			if (star.parentElement.classList.contains('permanent-selected')) {
				star.parentElement.classList.add('selected');
			}
		});
	}
});


/*
** Events Page: assigns the event title to the hidden field of the form.
*/
document.addEventListener('DOMContentLoaded', function() {
	// Select all buttons with the data-event attribute
	const botones = document.querySelectorAll('button[data-event]');

	// Add an event listener to each button
	botones.forEach(function(boton) {
		boton.addEventListener('click', function() {
			// Gets the value of the data-event attribute
			const eventValue = boton.getAttribute('data-event');

			// Search for the form inside the #event-meetup-form modal
			const modalForm = document.querySelector('#event-meetup-form');

			if (modalForm) {
				// Find the hidden field
				const hiddenField = modalForm.querySelector('.tm-hidden-event_name input[type="hidden"]');
				
				if (hiddenField) {
					// Assigns the value to the hidden field
					hiddenField.value = eventValue;
				}
			}
		});
	});
});


/*
** Enable dynamic validation for Gravity Forms fields
*/
document.addEventListener('DOMContentLoaded', function() {
	var forms = document.querySelectorAll('form.tm-form');

	forms.forEach(function(form) {

		// Check if the form contains the hidden field with the value "login-user"
		var typeFormField = form.querySelector('.tm-hidden-type_form input');
		if (typeFormField && (typeFormField.value === 'login-user' || typeFormField.value == 'forgot-password' || typeFormField.value == 'reset-password'  || typeFormField.value == 'change-password')) {
			return; // Do not apply dynamic validation to these types of forms
		}

		var submitButton = form.querySelector('input[type="submit"], button[type="submit"]');
		submitButton.disabled = true;

		form.querySelectorAll('input, select, textarea').forEach(function(field) {
			if (field.type === 'checkbox' || field.type === 'radio') {
				field.addEventListener('blur', function() {
					validateField(form, field, submitButton);
				});
				field.addEventListener('change', function() {
					validateField(form, field, submitButton);
				});
			} else {
				field.addEventListener('blur', function() {
					validateField(form, field, submitButton);
				});
			}
		});
	});

	function validateField(form, field, submitButton) {
		var formData = new FormData();

		var fieldName = field.name;
		var formId = field.closest('form').dataset.formid;

		if (field.type === 'radio') {
			var radioButtons = form.querySelectorAll('input[name="' + fieldName + '"]');
			var fieldChecked = Array.from(radioButtons).some(radio => radio.checked);
			formData.append('form_id', formId);
			formData.append('field_id', field.id);
			formData.append('field_value', fieldChecked);
		} else {
			var fieldId = field.id.split('_')[2];
			formData.append('form_id', formId);
			formData.append('field_id', fieldId);
			formData.append('field_value', field.type === 'checkbox' ? field.checked : field.value);
		}

		fetch('/wp-json/custom/v1/validate-field', {
			method: 'POST',
			body: formData
		})
		.then(function(response) {
			return response.json();
		})
		.then(function(validationResult) {
			handleValidationResult(form, validationResult, submitButton, field);
		});
	}

	function handleValidationResult(form, validationResult, submitButton, field) {
		// Verify that the formId and fieldId are valid
		if (!form || !validationResult || !validationResult.field_id) {
			return;
		}

		var formId = form.dataset.formid;
		var fieldId = validationResult.field_id;
		
		// Verify that the field container exists
		var fieldContainer = form.querySelector('#field_' + formId + '_' + fieldId);
		if (!fieldContainer) {
			return;
		}

		var validationMessageContainer = fieldContainer.querySelector('.gfield_validation_message');

		// Verify that the validation message container exists
		if (!validationMessageContainer) {
			validationMessageContainer = document.createElement('div');
			validationMessageContainer.className = 'gfield_description validation_message gfield_validation_message';
			fieldContainer.appendChild(validationMessageContainer);
		}

		if (validationResult.error) {
			validationMessageContainer.innerHTML = validationResult.message;
		} else if (field.classList.contains('iti__tel-input')) {
			handlePhoneValidation(field, validationMessageContainer);
		} else {
			validationMessageContainer.remove();
		}

		checkFormValidity(form, submitButton);
	}

	function handlePhoneValidation(field, validationMessageContainer) {
		if (field.value.trim() !== '') {
			if (field.classList.contains('error')) {
				validationMessageContainer.innerHTML = 'Invalid format';
			} else {
				validationMessageContainer.innerHTML = '';
			}
		}
	}

	function checkFormValidity(form, submitButton) {
		var allValid = true;

		form.querySelectorAll('.gfield').forEach(function(fieldContainer) {
			var containsRequired = fieldContainer.classList.contains('gfield_contains_required');
			var validationMessage = fieldContainer.querySelector('.validation_message');

			if (containsRequired) {
				var field = fieldContainer.querySelector('input:not(.iti__search-input), select, textarea');
				if (field.type === 'checkbox') {
					// Check if at least one checkbox in the group is selected
					var checkboxes = fieldContainer.querySelectorAll('input[type="checkbox"]');
					var isAnyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
					if (!isAnyChecked) {
						allValid = false;
					}
				} else if (field.type === 'radio') {
					// Check if at least one radio button in the group is selected
					var radioButtons = form.querySelectorAll('input[name="' + field.name + '"]');
					var isRadioChecked = Array.from(radioButtons).some(radio => radio.checked);
					if (!isRadioChecked) {
						allValid = false;
					}
				} else if (!field.value.trim() || (validationMessage && validationMessage.innerHTML !== '')) {
					allValid = false;
				}
			} else if (validationMessage && validationMessage.innerHTML !== '') {
				var isVisible = window.getComputedStyle(validationMessage).display !== 'none';
				if (isVisible) {
					allValid = false;
				}
			}

			if (fieldContainer.querySelector('input.iti__tel-input') && fieldContainer.querySelector('input.iti__tel-input').classList.contains('error')) {
				allValid = false;
			}
		});
		submitButton.disabled = !allValid;
	}
});

// Add placeholders to the login form fields and hide labels
document.addEventListener('DOMContentLoaded', function () {

	const loginForm = document.getElementById('loginform');

	if (loginForm) {
		// Hide labels and add placeholders to inputs
		const usernameField = document.getElementById('user_login');
		const passwordField = document.getElementById('user_pass');
		
		// Remove the labels (if they exist)
		const usernameLabel = loginForm.querySelector('label[for="user_login"]');
		const passwordLabel = loginForm.querySelector('label[for="user_pass"]');
		if (usernameLabel) usernameLabel.style.display = 'none';
		if (passwordLabel) passwordLabel.style.display = 'none';

		// Add placeholders to the fields
		if (usernameField) usernameField.placeholder = 'Username or E-mail';
		if (passwordField) passwordField.placeholder = 'Password';
	}
});


// Automatically check the consent checkbox and hide it if the form type is "update-user"
document.addEventListener('DOMContentLoaded', function () {
	
	const typeFormField = document.querySelector('.tm-hidden-type_form input[type="hidden"]');
	
	if (typeFormField && ( typeFormField.value === 'update-user' || typeFormField.value === 'change-password' )) {
		
		const consentFieldContainer = document.querySelector('.gfield--type-consent');

		// Checkbox in Consent field
		const consentCheckbox = consentFieldContainer?.querySelector('input[type="checkbox"]');

		// Checks the checkbox and ensures it passes Gravity Forms validation
		if (consentCheckbox) {
			consentCheckbox.checked = true;

			// Simulates a state change so that Gravity Forms detects and validates the field
			const event = new Event('change', { bubbles: true });
			consentCheckbox.dispatchEvent(event);

			// Hide the Consent field container
			consentFieldContainer.style.display = 'none';
		}
	}
});

// Remove the "send" parameter from the URL after submitting the form
if (window.location.search.includes('send=1')) {
	const url = new URL(window.location);
	url.searchParams.delete('send');
	window.history.replaceState({}, document.title, url.toString());
}

// Focus on the username field when the login modal is opened
document.addEventListener('DOMContentLoaded', function() {

	const loginLinks = document.querySelectorAll('a[href="#login-modal"]');
	
	loginLinks.forEach(link => {
		link.addEventListener('click', function() {
			const userField = document.getElementById('user_login');
			if (userField) {
				setTimeout(() => {
					userField.focus();
				}, 100);
			}
		});
	});
});
