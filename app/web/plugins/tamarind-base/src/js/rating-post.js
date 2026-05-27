/**
 * Rating System for Posts
 * Handles star rating functionality with AJAX submission
 */
(function() {
    'use strict';
    
    // Exit if no rating data or user not logged in
    if (typeof ratingUserData === 'undefined' || !ratingUserData.is_logged_in) {
        return;
    }
    
    // DOM elements
    const ratingContainer = document.querySelector('.simple-rating-container');
    if (!ratingContainer) return;
    
    const starInputs = ratingContainer.querySelectorAll('.rating-star-input');
    const starLabels = ratingContainer.querySelectorAll('.rating-star-label');
    const starsGroup = ratingContainer.querySelector('.rating-stars');
    const loadingIndicator = ratingContainer.querySelector('.loading-indicator');
    const messageContainer = ratingContainer.querySelector('.rating-message');
    
    // State variables
    let currentRating = 0;
    let isSubmitting = false;
    let hasVoted = false;
    let isHovering = false;
    let hoverRating = 0;
    
    /**
     * Get cookie value by name
     */
    function getCookie(name) {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            cookie = cookie.trim();
            if (cookie.startsWith(name + '=')) {
                return cookie.substring(name.length + 1);
            }
        }
        return null;
    }
    
    /**
     * Set cookie with 1 year expiration
     */
    function setCookie(name, value) {
        const expires = new Date();
        expires.setFullYear(expires.getFullYear() + 1);
        document.cookie = `${name}=${value}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
    }
    
    /**
     * Check if user has already rated this post
     */
    function checkIfUserHasRated() {
        const cookieName = `rating_${ratingUserData.post_id}_${ratingUserData.id}`;
        const cookieValue = getCookie(cookieName);
        
        if (cookieValue) {
            return {
                hasRated: true,
                rating: parseInt(cookieValue, 10)
            };
        }
        
        return {
            hasRated: false,
            rating: 0
        };
    }
    
    /**
     * Save rating to cookie
     */
    function saveRatingToCookie(rating) {
        const cookieName = `rating_${ratingUserData.post_id}_${ratingUserData.id}`;
        setCookie(cookieName, rating.toString());
    }
    
    /**
     * Create spinner HTML with CSS
     */
    function createSpinnerHTML() {
        const spinnerWrapper = document.createElement('span');
        spinnerWrapper.className = 'rating-spinner-wrapper';
        
        const spinner = document.createElement('span');
        spinner.className = 'rating-spinner';
        
        const text = document.createElement('span');
        text.className = 'loading-text';
        text.textContent = ratingUserData.saving_message || 'Saving your rating...';
        
        spinnerWrapper.appendChild(spinner);
        spinnerWrapper.appendChild(text);
        
        return spinnerWrapper;
    }
    
    /**
     * Initialize the rating system
     */
    function initRatingSystem() {
        // Check if user has already rated
        const ratingCheck = checkIfUserHasRated();
        
        if (ratingCheck.hasRated) {
            currentRating = ratingCheck.rating;
            hasVoted = true;
            
            // Check the corresponding radio button
            const checkedInput = ratingContainer.querySelector(`.rating-star-input[value="${currentRating}"]`);
            if (checkedInput) {
                checkedInput.checked = true;
                checkedInput.setAttribute('aria-checked', 'true');
            }
            
            // Highlight the correct stars (up to the voted rating)
            highlightSelectedStars(currentRating);
            
            showMessage(
                ratingUserData.already_voted_message || 'You have already rated this content. Thank you for your feedback.',
                'info'
            );
            disableStars();
            return;
        }
        
        // Add event listeners to star inputs
        setupStarListeners();
    }
    
    /**
     * Setup star event listeners
     */
    function setupStarListeners() {
        // Remove existing listeners
        starInputs.forEach(input => {
            input.removeEventListener('click', handleStarClick);
            input.removeEventListener('keydown', handleStarKeydown);
            input.removeEventListener('focus', handleStarFocus);
            input.removeEventListener('blur', handleStarBlur);
        });
        
        // Remove hover listeners from labels
        starLabels.forEach(label => {
            label.removeEventListener('mouseenter', handleLabelMouseEnter);
            label.removeEventListener('mouseleave', handleLabelMouseLeave);
        });
        
        // Add new listeners to each radio input
        starInputs.forEach(input => {
            input.addEventListener('click', handleStarClick);
            input.addEventListener('keydown', handleStarKeydown);
            input.addEventListener('focus', handleStarFocus);
            input.addEventListener('blur', handleStarBlur);
        });
        
        // Add hover listeners to labels
        starLabels.forEach((label, index) => {
            label.addEventListener('mouseenter', () => handleLabelMouseEnter(index + 1));
            label.addEventListener('mouseleave', handleLabelMouseLeave);
        });
    }
    
    /**
     * Handle star click
     */
    function handleStarClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (hasVoted) {
            showMessage(ratingUserData.already_voted_message || 'You have already voted for this content.', 'info');
            return;
        }
        
        if (isSubmitting) return;
        
        const rating = parseInt(e.target.value, 10);
        currentRating = rating;
        
        // Update aria-checked for accessibility
        starInputs.forEach(input => {
            input.setAttribute('aria-checked', input.value === rating.toString() ? 'true' : 'false');
        });
        
        submitRating(currentRating);
    }
    
    /**
     * Handle keyboard navigation
     */
    function handleStarKeydown(e) {
        if (hasVoted || isSubmitting) {
            e.preventDefault();
            return;
        }
        
        const currentIndex = Array.from(starInputs).findIndex(input => input === document.activeElement);
        
        switch(e.key) {
            case 'ArrowRight':
            case 'ArrowUp':
                e.preventDefault();
                const nextIndex = (currentIndex + 1) % starInputs.length;
                starInputs[nextIndex].focus();
                hoverRating = nextIndex + 1;
                highlightStarsOnHover(hoverRating);
                break;
                
            case 'ArrowLeft':
            case 'ArrowDown':
                e.preventDefault();
                const prevIndex = (currentIndex - 1 + starInputs.length) % starInputs.length;
                starInputs[prevIndex].focus();
                hoverRating = prevIndex + 1;
                highlightStarsOnHover(hoverRating);
                break;
                
            case 'Enter':
            case ' ':
                e.preventDefault();
                const rating = parseInt(e.target.value, 10);
                currentRating = rating;
                
                // Update aria-checked
                starInputs.forEach(input => {
                    input.setAttribute('aria-checked', input.value === rating.toString() ? 'true' : 'false');
                });
                
                submitRating(currentRating);
                break;
                
            case 'Escape':
                e.preventDefault();
                hoverRating = 0;
                highlightStarsOnSelection(currentRating);
                break;
        }
    }
    
    /**
     * Handle focus on star
     */
    function handleStarFocus(e) {
        if (!hasVoted && !isSubmitting) {
            const rating = parseInt(e.target.value, 10);
            hoverRating = rating;
            highlightStarsOnHover(rating);
        }
    }
    
    /**
     * Handle blur on star
     */
    function handleStarBlur() {
        if (!hasVoted && !isSubmitting) {
            hoverRating = 0;
            highlightStarsOnSelection(currentRating);
        }
    }
    
    /**
     * Handle mouse enter on label
     */
    function handleLabelMouseEnter(rating) {
        if (hasVoted || isSubmitting) return;
        
        isHovering = true;
        hoverRating = rating;
        highlightStarsOnHover(rating);
    }
    
    /**
     * Handle mouse leave from label
     */
    function handleLabelMouseLeave() {
        if (hasVoted || isSubmitting) return;
        
        isHovering = false;
        hoverRating = 0;
        highlightStarsOnSelection(currentRating);
    }
    
    /**
     * Highlight stars on hover (for mouse and keyboard)
     */
    function highlightStarsOnHover(rating) {
        // Remove all checked states first
        starInputs.forEach(input => {
            input.checked = false;
        });
        
        // Highlight stars up to the hover rating
        starLabels.forEach((label, index) => {
            const visual = label.querySelector('.rating-star-visual');
            if (!visual) return;
            
            // Remove all classes
            visual.classList.remove('selected', 'hovered');
            
            // Highlight if this star is <= hover rating
            if (index < rating) {
                visual.classList.add('hovered');
            }
        });
        
        // Add a temporary class to the stars group for CSS hover effect
        starsGroup.classList.add('hovering');
    }
    
    /**
     * Highlight stars based on selection (actual rating)
     */
    function highlightStarsOnSelection(rating) {
        // Remove hover classes
        starLabels.forEach(label => {
            const visual = label.querySelector('.rating-star-visual');
            if (visual) {
                visual.classList.remove('hovered');
            }
        });
        
        // Remove hovering class from group
        starsGroup.classList.remove('hovering');
        
        // Highlight selected stars
        highlightSelectedStars(rating);
    }
    
    /**
     * Highlight selected stars (up to the given rating)
     */
    function highlightSelectedStars(rating) {
        // Check the appropriate radio button if we have a rating
        if (rating > 0) {
            const input = ratingContainer.querySelector(`.rating-star-input[value="${rating}"]`);
            if (input) {
                input.checked = true;
            }
            
            // Highlight all stars up to the rating
            starLabels.forEach((label, index) => {
                const visual = label.querySelector('.rating-star-visual');
                if (visual) {
                    // Remove all classes first
                    visual.classList.remove('selected', 'hovered');
                    
                    // Add selected class to stars up to the rating
                    if (index < rating) {
                        visual.classList.add('selected');
                    }
                }
            });
        } else {
            // If no rating, remove all selections
            starInputs.forEach(input => {
                input.checked = false;
                input.removeAttribute('aria-checked');
            });
            
            starLabels.forEach(label => {
                const visual = label.querySelector('.rating-star-visual');
                if (visual) {
                    visual.classList.remove('selected', 'hovered');
                }
            });
        }
    }
    
    /**
     * Disable all stars (after voting)
     */
    function disableStars() {
        starInputs.forEach(input => {
            input.disabled = true;
            input.setAttribute('aria-disabled', 'true');
            input.setAttribute('tabindex', '-1');
        });
        
        starLabels.forEach(label => {
            label.style.cursor = 'not-allowed';
            label.classList.add('disabled');
        });
        
        starsGroup.classList.add('disabled');
        
        highlightSelectedStars(currentRating);
    }
    
    /**
     * Show loading indicator
     */
    function showLoadingIndicator() {
        if (loadingIndicator) {
            // Clear any existing content
            loadingIndicator.innerHTML = '';
            
            // Add spinner and text
            const spinnerHTML = createSpinnerHTML();
            loadingIndicator.appendChild(spinnerHTML);
            
            // Show the indicator
            loadingIndicator.style.display = 'flex';
            loadingIndicator.style.alignItems = 'center';
            loadingIndicator.style.gap = '10px';
        }
    }
    
    /**
     * Hide loading indicator
     */
    function hideLoadingIndicator() {
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
            loadingIndicator.innerHTML = '';
        }
    }
    
    /**
     * Submit rating via AJAX
     */
    async function submitRating(rating) {
        if (hasVoted || isSubmitting) return;
        
        // Set flags immediately
        hasVoted = true;
        isSubmitting = true;
        
        // Update UI immediately
        showLoadingIndicator();
        hideMessage();
        disableStars();
        
        try {
            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'save_rating');
            formData.append('rating', rating);
            formData.append('post_id', ratingUserData.post_id);
            formData.append('user_id', ratingUserData.id);
            formData.append('user_name', ratingUserData.name);
            formData.append('user_email', ratingUserData.email);
            formData.append('nonce', ratingUserData.nonce);
            
            // Send AJAX request
            const response = await fetch(ratingUserData.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Save to cookie
                saveRatingToCookie(rating);
                
                // Hide loading indicator after success
                hideLoadingIndicator();
                
                // Ensure only the correct stars are highlighted
                highlightSelectedStars(rating);
                
                // Show success message
                showMessage(ratingUserData.success_message || 'Thank you for your rating!', 'success');
            } else {
                throw new Error(result.data || 'Error saving rating');
            }
            
        } catch (error) {
            console.error('Error submitting rating:', error);
            
            // On error, allow user to try again
            hasVoted = false;
            isSubmitting = false;
            hideLoadingIndicator();
            
            // Re-enable stars
            starInputs.forEach(input => {
                input.disabled = false;
                input.removeAttribute('aria-disabled');
                input.setAttribute('tabindex', '0');
                input.checked = false;
            });
            
            starLabels.forEach(label => {
                label.style.cursor = 'pointer';
                label.classList.remove('disabled');
            });
            
            starsGroup.classList.remove('disabled');
            
            // Reset and re-add event listeners
            currentRating = 0;
            hoverRating = 0;
            highlightSelectedStars(0);
            setupStarListeners();
            
            showMessage(ratingUserData.error_message || 'Error saving your rating. Please try again.', 'error');
        }
    }
    
    /**
     * Show message to user
     */
    function showMessage(text, type = 'info') {
        if (!messageContainer) return;
        
        messageContainer.textContent = text;
        messageContainer.className = `rating-message box-info ${type}`;
        messageContainer.style.display = 'block';
        
        // Auto-hide non-error messages after 5 seconds
        if (type !== 'error') {
            setTimeout(hideMessage, 5000);
        }
    }
    
    /**
     * Hide message
     */
    function hideMessage() {
        if (messageContainer) {
            messageContainer.style.display = 'none';
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRatingSystem);
    } else {
        setTimeout(initRatingSystem, 100);
    }
    
})();