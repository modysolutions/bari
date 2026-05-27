/**
 * TMModal - UIkit Modal replacement using native HTMLDialogElement
 * Handles modal functionality using the native <dialog> element
 * while maintaining tm-modal class naming convention
 * 
 * Usage:
 * - Add class "tm-modal" to <dialog> elements to define modals
 * - Use elements with class "tm-modal-trigger" and href or data attribute
 *   to open modals
 * - Use elements with class "tm-modal-close" to close modals
 * 
 * Trigger:
   <a href="#login-modal" class="tm-modal-trigger">Login</a>
   <button data-tm-modal-target="#event-meetup-form" class="tm-modal-trigger">Open</button>
 
   Modal:
   <dialog id="login-modal" class="tm-modal">
    <div class="tm-modal-content">
        <button class="tm-modal-close" type="button">×</button>
        <!-- content -->
    </div>
   </dialog>
 
   Auto-Open Modals:
   Modals can automatically open after a specified delay using the data-tm-auto-open attribute:
   <dialog id="welcome-modal" class="tm-modal" data-tm-auto-open="3000">

   Programmatic Control:
   Modals can be controlled programmatically through JavaScript:
   // Open modal by ID
   window.tmModal.openModalById('#my-modal');

   // Close modal by ID
   window.tmModal.closeModalById('#my-modal');

   // Close all open modals
   window.tmModal.closeAllModals();

   Event System:
   TMModal dispatches custom events for integration with other scripts:
   // Listen for modal open
   document.addEventListener('tm-modal-open', (e) => {
     console.log('Modal opened:', e.target);
   });

   // Listen for modal close  
   document.addEventListener('tm-modal-close', (e) => {
     console.log('Modal closed:', e.target);
   });   
 */

class TMModal {
    constructor() {
        this.init();
    }

    /**
     * Initialize event listeners and modal functionality
     */
    init() {
        // Event delegation for modal triggers
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('.tm-modal-trigger');
            if (trigger) {
                e.preventDefault();
                this.handleTrigger(trigger);
            }

            // Close modal when close button is clicked
            const closeBtn = e.target.closest('.tm-modal-close');
            if (closeBtn) {
                e.preventDefault();
                this.closeModal(closeBtn.closest('dialog'));
            }
        });

        // Add click event to each modal for backdrop closing
        this.initializeModals();
        
        // Initialize auto-open modals
        this.initializeAutoOpenModals();
    }

    /**
     * Initialize individual modal events
     */
    initializeModals() {
        document.querySelectorAll('.tm-modal').forEach(modal => {
            // Close modal when clicking on backdrop
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal);
                }
            });
        });
    }

    /**
     * Initialize modals that should open automatically
     */
    initializeAutoOpenModals() {
        document.querySelectorAll('.tm-modal[data-tm-auto-open]').forEach(modal => {
            const delay = parseInt(modal.dataset.tmAutoOpen) || 0;
            
            setTimeout(() => {
                this.openModal(modal);
            }, delay);
        });
    }

    /**
     * Handle modal trigger click
     * @param {HTMLElement} trigger - The clicked trigger element
     */
    handleTrigger(trigger) {
        let modalId;
        
        // Get target modal ID from data attribute or href
        if (trigger.dataset.tmModalTarget) {
            modalId = trigger.dataset.tmModalTarget;
        } else if (trigger.hash) {
            modalId = trigger.hash;
        }

        if (modalId) {
            const modal = document.querySelector(modalId);
            // Verify it's a dialog element with tm-modal class
            if (modal && modal.tagName === 'DIALOG' && modal.classList.contains('tm-modal')) {
                this.openModal(modal);
            }
        }
    }

    /**
     * Open a modal dialog
     * @param {HTMLDialogElement} modal - The dialog element to open
     */
    openModal(modal) {
        // Close any previously opened modals
        this.closeAllModals();
        
        // Use native showModal() method for proper dialog functionality
        modal.showModal();
        
        // Dispatch custom event for potential integrations
        modal.dispatchEvent(new CustomEvent('tm-modal-open'));
    }

    /**
     * Close a modal dialog
     * @param {HTMLDialogElement} modal - The dialog element to close
     */
    closeModal(modal) {
        if (modal && modal.open) {
            // Use native close() method
            modal.close();
            // Dispatch custom event
            modal.dispatchEvent(new CustomEvent('tm-modal-close'));
        }
    }

    /**
     * Close all currently open modals
     */
    closeAllModals() {
        document.querySelectorAll('.tm-modal[open]').forEach(modal => {
            this.closeModal(modal);
        });
    }

    /**
     * Manual method to open modal by ID (for external calls)
     * @param {string} modalId - The ID of the modal to open
     */
    openModalById(modalId) {
        const modal = document.querySelector(modalId);
        if (modal && modal.tagName === 'DIALOG') {
            this.openModal(modal);
        }
    }

    /**
     * Manual method to close modal by ID (for external calls)
     * @param {string} modalId - The ID of the modal to close
     */
    closeModalById(modalId) {
        const modal = document.querySelector(modalId);
        if (modal) {
            this.closeModal(modal);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.tmModal = new TMModal();
});