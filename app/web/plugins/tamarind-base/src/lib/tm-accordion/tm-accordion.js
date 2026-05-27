class TmAccordion extends HTMLElement {
	constructor() {
		super();
		this._ready = false;
		this._observer = new MutationObserver(this.initAccordion.bind(this));
	}

	connectedCallback() {
		// Set up the observer to detect changes in the internal DOM
		this._observer.observe(this, {
			childList: true,
			subtree: true
		});

		// Attempt to initialize immediately
		this.initAccordion();
	}

	disconnectedCallback() {
		this._observer.disconnect();
	}

	initAccordion() {
		if (this._ready) return;

		const items = this.querySelectorAll('.accordion-item');
		if (items.length === 0) return; // No elements yet

		this._ready = true;
		this._observer.disconnect();

		// Accordion initialization
		this.setAttribute('role', 'region');
		this.setAttribute('aria-label', 'Accordion');

		items.forEach((item, index) => {
			const button = item.querySelector('.menu-title:not(.menu-title-link)');
			const panel = item.querySelector('.menu-options');

			if (button && panel) {
				const itemId = `accordion-item-${index}`;

				// Set up ARIA
				button.setAttribute('id', `${itemId}-button`);
				button.setAttribute('aria-expanded', 'false');
				button.setAttribute('aria-controls', `${itemId}-panel`);
				button.setAttribute('type', 'button');

				panel.setAttribute('id', `${itemId}-panel`);
				panel.setAttribute('aria-labelledby', `${itemId}-button`);
				panel.setAttribute('role', 'region');
				panel.setAttribute('hidden', '');

				// Activate if it has .active
				if (panel.querySelector('.active') || item.classList.contains('is-expanded')) {
					this.activateItem(item);
				}
			}
		});

		// Set up click event after initialization
		this.addEventListener('click', this.handleClick.bind(this));
	}

	handleClick(e) {
		const button = e.target.closest('.menu-title:not(.menu-title-link)');
		if (!button) return;

		const item = button.closest('.accordion-item');
		if (item.classList.contains('open')) {
			this.deactivateItem(item);
		} else {
			this.querySelectorAll('.accordion-item.open').forEach(openItem => {
				if (openItem !== item) this.deactivateItem(openItem);
			});
			this.activateItem(item);
		}
	}

	activateItem(item) {
		const button = item.querySelector('.menu-title');
		const panel = item.querySelector('.menu-options');

		button.setAttribute('aria-expanded', 'true');
		panel.removeAttribute('hidden');
		item.classList.add('open');

		panel.style.maxHeight = panel.scrollHeight + "px";
	}

	deactivateItem(item) {
		const button = item.querySelector('.menu-title');
		const panel = item.querySelector('.menu-options');

		button.setAttribute('aria-expanded', 'false');
		panel.setAttribute('hidden', '');
		item.classList.remove('open');

		panel.style.maxHeight = null;
	}
}

customElements.define('tm-accordion', TmAccordion);