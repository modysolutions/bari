/**
 * TMPolaris - Floating navigation toggle widget
 *
 * Renders a floating action button that toggles navigation between the
 * homepage and a configured internal landing target.
 *
 * Usage:
 * The widget is rendered by PHP in the page footer as a container div:
 *
 *   <div class="tm-polaris-widget"
 *        style="--tm-polaris-bottom: 32px"
 *        data-state="on-home"
 *        data-position="right"
 *        data-device="all">
 *   </div>
 */
class TMPolaris {
    constructor( container ) {
        this.widget = container;
        this._applyPosition();
        this._applyVisibility();
        window.addEventListener( 'resize', () => this._applyVisibility() );
    }

    _applyPosition() {
        const position = this.widget.dataset.position || 'right';
        this.widget.classList.add( 'tm-polaris-widget--' + position );
    }

    _isDeviceVisible() {
        const device   = this.widget.dataset.device || 'all';
        const isMobile = window.matchMedia( '(max-width: 767px)' ).matches;

        if ( device === 'all' ) {
            return true;
        }

        if ( device === 'mobile' && isMobile ) {
            return true;
        }

        if ( device === 'desktop' && ! isMobile ) {
            return true;
        }

        return false;
    }

    _applyVisibility() {
        this.widget.classList.toggle( 'tm-polaris-widget--hidden', ! this._isDeviceVisible() );
    }
}

// Initialise when DOM is ready.
document.addEventListener( 'DOMContentLoaded', () => {
    const container = document.querySelector( '.tm-polaris-widget' );
    if ( container ) {
        window.tmPolaris = new TMPolaris( container );
    }
} );
