class TmTabs extends HTMLElement {
	constructor() {
		super();
		this._ready = false;
		this._observer = new MutationObserver(this.initTabs.bind(this));
	}
  
	connectedCallback() {
		// Set up the observer to detect changes.
		this._observer.observe(this, {
			childList: true,
			subtree: true
		});
  
		// Try to initialize immediately.
		this.initTabs();
	}
  
	disconnectedCallback() {
		this._observer.disconnect();
	}
  
	initTabs() {
		if (this._ready) return;

		const tabs = this.querySelectorAll('.tab-title');
		if (tabs.length === 0) return; // No elements yet.
  
		this._ready = true;
		this._observer.disconnect();
  
		// Initial setup.
		this.setAttribute('role', 'tablist');
  
		// Initialize all tabs.
		tabs.forEach((tab, index) => {
			const tabId = tab.id || `tab-${index}`;
			const panelId = tab.getAttribute('data-target') || `tabpanel-${index}`;
			const panel = this.querySelector(`#${panelId}`);
		
			// Set ARIA attributes for the tab.
			tab.setAttribute('role', 'tab');
			tab.setAttribute('aria-selected', 'false');
			tab.setAttribute('aria-controls', panelId);
			tab.setAttribute('tabindex', '-1');
			if (!tab.id) tab.id = tabId;
	
			// Configure panel if it exists.
			if (panel) {
				panel.setAttribute('role', 'tabpanel');
				panel.setAttribute('aria-labelledby', tabId);
				panel.setAttribute('tabindex', '0');
				panel.setAttribute('hidden', '');
			}
		});
  
		// Activate the active tab or the first one.
		const activeTab = this.querySelector('.tab-title.active') || tabs[0];
		if (activeTab) {
			this.activateTab(activeTab);
		}
  
		// Set up click event.
		this.addEventListener('click', this.handleClick.bind(this));
		this.addEventListener('keydown', this.handleKeyDown.bind(this));
	}
  
	handleClick(e) {
		const target = e.target.closest('.tab-title');
		if (target) {
			this.activateTab(target);
		}
	}
  
	activateTab(tab) {
		const tabs = this.querySelectorAll('.tab-title');
		const targetPanelId = tab.getAttribute('data-target') || tab.getAttribute('aria-controls');
		const targetPanel = targetPanelId ? this.querySelector(`#${targetPanelId}`) : null;
  
		// Deactivate all tabs.
		tabs.forEach(t => {
			t.classList.remove('active');
			t.setAttribute('aria-selected', 'false');
			t.setAttribute('tabindex', '-1');
		});
  
		// Hide all panels.
		this.querySelectorAll('.tab-content').forEach(panel => {
			panel.classList.remove('active');
			panel.setAttribute('hidden', '');
		});
  
		// Activate selected tab.
		tab.classList.add('active');
		tab.setAttribute('aria-selected', 'true');
		tab.setAttribute('tabindex', '0');
  
		// Show associated panel.
		if (targetPanel) {
			targetPanel.classList.add('active');
			targetPanel.removeAttribute('hidden');
		}
	}
  
	handleKeyDown(e) {
		const target = e.target;
		if (!target.classList.contains('tab-title')) return;
  
		const tabs = Array.from(this.querySelectorAll('.tab-title'));
		const currentIndex = tabs.indexOf(target);
  
		let nextTab;
		switch (e.key) {
			case 'ArrowLeft':
				e.preventDefault();
				nextTab = tabs[currentIndex - 1] || tabs[tabs.length - 1];
				break;
			case 'ArrowRight':
				e.preventDefault();
				nextTab = tabs[currentIndex + 1] || tabs[0];
				break;
			case 'Home':
				e.preventDefault();
				nextTab = tabs[0];
				break;
			case 'End':
				e.preventDefault();
				nextTab = tabs[tabs.length - 1];
				break;
			default:
				return;
		}
  
		this.activateTab(nextTab);
		nextTab.focus();
	}
  }
  
  customElements.define('tm-tabs', TmTabs);
