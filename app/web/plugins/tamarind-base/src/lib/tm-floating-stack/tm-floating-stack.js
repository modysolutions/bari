/**
 * TMFloatingStack - Shared positioning manager for floating widgets
 *
 * Keeps WhatsApp and Polaris stacked consistently when both are visible
 * on the same side of the viewport.
 */
class TMFloatingStack {
    constructor() {
        this.widgets = Array.from(
            document.querySelectorAll( '.tm-whatsapp-widget, .tm-polaris-widget' )
        );

        if ( ! this.widgets.length ) {
            return;
        }

        this.rafId = null;
        this.observer = new MutationObserver( () => this._scheduleRefresh() );

        this.widgets.forEach( ( widget ) => {
            this.observer.observe( widget, {
                attributes: true,
                attributeFilter: [ 'class', 'style' ],
            } );
        } );

        window.addEventListener( 'resize', () => this._scheduleRefresh() );
        document.addEventListener( 'tm-whatsapp-open', () => this._scheduleRefresh() );
        document.addEventListener( 'tm-whatsapp-close', () => this._scheduleRefresh() );

        this._scheduleRefresh();
    }

    _scheduleRefresh() {
        if ( this.rafId ) {
            cancelAnimationFrame( this.rafId );
        }

        this.rafId = requestAnimationFrame( () => {
            this.rafId = null;
            this._refresh();
        } );
    }

    _refresh() {
        const gap = window.matchMedia( '(max-width: 767px)' ).matches ? 12 : 16;
        const openPopupSides = this._getOpenPopupSides();
        const groups = {
            left: [],
            right: [],
        };

        this.widgets.forEach( ( widget ) => {
            this._setStackOffset( widget, 0 );
            this._setPopupOverlapState( widget, openPopupSides.has( this._getPosition( widget ) ) );

            if ( ! this._isVisible( widget ) ) {
                return;
            }

            groups[ this._getPosition( widget ) ].push( widget );
        } );

        Object.values( groups ).forEach( ( widgets ) => {
            let minBottom = null;

            widgets
                .sort( ( a, b ) => this._getPriority( a ) - this._getPriority( b ) )
                .forEach( ( widget, index ) => {
                    const baseBottom = this._getBaseBottom( widget );
                    const offset = null === minBottom ? 0 : Math.max( 0, minBottom - baseBottom );
                    const effectiveBottom = baseBottom + offset;

                    this._setStackOffset( widget, offset );

                    minBottom = effectiveBottom + this._getAnchorHeight( widget );

                    if ( index < widgets.length - 1 ) {
                        minBottom += gap;
                    }
                } );
        } );
    }

    _isVisible( widget ) {
        if ( ! widget.isConnected ) {
            return false;
        }

        const styles = window.getComputedStyle( widget );
        if ( styles.display === 'none' || styles.visibility === 'hidden' ) {
            return false;
        }

        const rect = widget.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
    }

    _getPosition( widget ) {
        return widget.dataset.position === 'left' ? 'left' : 'right';
    }

    _getPriority( widget ) {
        if ( widget.classList.contains( 'tm-whatsapp-widget' ) ) {
            return 0;
        }

        if ( widget.classList.contains( 'tm-polaris-widget' ) ) {
            return 1;
        }

        return 99;
    }

    _getOpenPopupSides() {
        const sides = new Set();

        this.widgets.forEach( ( widget ) => {
            if ( ! widget.classList.contains( 'tm-whatsapp-widget' ) || ! this._isVisible( widget ) ) {
                return;
            }

            const popup = widget.querySelector( '.tm-whatsapp-popup' );

            if ( popup && popup.classList.contains( 'tm-whatsapp-popup--open' ) ) {
                sides.add( this._getPosition( widget ) );
            }
        } );

        return sides;
    }

    _getAnchorHeight( widget ) {
        const anchor = widget.querySelector( '.tm-whatsapp-fab, .tm-polaris-fab' );

        if ( ! anchor ) {
            return Math.ceil( widget.getBoundingClientRect().height );
        }

        return Math.ceil( anchor.getBoundingClientRect().height );
    }

    _getBaseBottom( widget ) {
        const bottom = parseFloat( window.getComputedStyle( widget ).bottom );
        return Number.isFinite( bottom ) ? bottom : 0;
    }

    _setStackOffset( widget, offset ) {
        const value = `${ Math.max( 0, Math.ceil( offset ) ) }px`;

        if ( widget.style.getPropertyValue( '--tm-widget-stack-offset' ) === value ) {
            return;
        }

        widget.style.setProperty( '--tm-widget-stack-offset', value );
    }

    _setPopupOverlapState( widget, shouldHide ) {
        if ( ! widget.classList.contains( 'tm-polaris-widget' ) ) {
            return;
        }

        widget.classList.toggle( 'tm-polaris-widget--stack-hidden', shouldHide );
    }
}

document.addEventListener( 'DOMContentLoaded', () => {
    window.tmFloatingStack = new TMFloatingStack();
} );
