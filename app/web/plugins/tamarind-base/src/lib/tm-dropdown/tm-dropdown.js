class TmDropdown extends HTMLElement {
    constructor() {
      super();
      this._ready = false;
      this._observer = new MutationObserver(() => this.initDropdown());
    }
  
    connectedCallback() {
      this._observer.observe(this, {
        childList: true,
        subtree: true
      });
      this.initDropdown();
    }
  
    disconnectedCallback() {
      this._observer.disconnect();
      document.removeEventListener('click', this.handleOutsideClick);
    }
  
    initDropdown() {
      if (this._ready) return;
      
      this.button = this.querySelector('.toggle-button');
      this.dropdown = this.querySelector('.dropdown');
      
      if (!this.button || !this.dropdown) return;
      
      this._ready = true;
      this._observer.disconnect();
  
      // Configuration ARIA.
      this.button.setAttribute('aria-haspopup', 'true');
      this.button.setAttribute('aria-expanded', 'false');
      this.button.setAttribute('aria-controls', 'dropdown-' + Date.now());
      this.dropdown.setAttribute('id', this.button.getAttribute('aria-controls'));
      this.dropdown.setAttribute('role', 'menu');
  
      // Event listeners.
      this.button.addEventListener('click', this.toggleDropdown.bind(this));
      this.handleOutsideClick = this.closeFromOutside.bind(this);
      document.addEventListener('click', this.handleOutsideClick);
  
      // Keyboard.
      this.button.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          this.toggleDropdown(e);
        }
      });
    }
  
    toggleDropdown(event) {
      event.preventDefault();
      event.stopPropagation();
      
      const isOpen = this.dropdown.classList.toggle('open');
      
      // Update ARIA.
      this.button.setAttribute('aria-expanded', String(isOpen));
      
      // Close other dropdowns.
      document.querySelectorAll('tm-dropdown .dropdown').forEach((el) => {
        if (el !== this.dropdown) {
          el.classList.remove('open');
          el.previousElementSibling?.setAttribute('aria-expanded', 'false');
        }
      });
    }
  
    closeFromOutside(event) {
      if (!this.contains(event.target)) {
        this.dropdown.classList.remove('open');
        this.button.setAttribute('aria-expanded', 'false');
      }
    }
  }
  
  customElements.define('tm-dropdown', TmDropdown);
