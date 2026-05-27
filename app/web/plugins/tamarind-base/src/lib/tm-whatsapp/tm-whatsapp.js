/**
 * TMWhatsapp - Floating WhatsApp chat widget
 *
 * Renders a floating action button that opens a branded welcome popup,
 * displays availability status based on a configurable schedule, and
 * redirects the user to wa.me with a pre-filled message on CTA click.
 *
 * Usage:
 * The widget is rendered by PHP in the page footer as a container div:
 *
 *   <div class="tm-whatsapp-widget"
 *        data-phone="34612345678"
 *        data-prefilled="Hello, I found you through ECig Intelligence."
 *        data-position="right"
 *        data-delay="3"
 *        data-device="all"
 *        data-fab-text="Chat with us"
 *        data-welcome-title="Chat with us"
 *        data-welcome-message="Hi! How can we help you today?"
 *        data-cta-text="Start Chat"
 *        data-away-cta-text="Leave a Message"
 *        data-away-message="We are currently away."
 *        data-away-next-availability="Back Monday at 9:00 AM (GMT)"
 *        data-timezone="Europe/London"
 *        data-schedule='[{"day":"monday","is_open":true,"open_time":"09:00","close_time":"18:00"}]'
 *        data-holidays='[{"date":"2026-12-25","label":"Christmas Day"}]'
 *        data-privacy-notice="By starting a chat, you accept our privacy policy."
 *        data-privacy-link="https://example.com/privacy">
 *   </div>
 *
 * Programmatic control:
 *   window.tmWhatsapp.openPopup();
 *   window.tmWhatsapp.closePopup();
 *
 * Events:
 *   document — 'tm-whatsapp-open'  (popup opened)
 *   document — 'tm-whatsapp-close' (popup closed)
 *   document — 'tm-whatsapp-chat'  (user clicked CTA / redirected to WhatsApp)
 */

class TMWhatsapp {

    /**
     * @param {HTMLElement} container - The .tm-whatsapp-widget element rendered by PHP.
     */
    constructor( container ) {
        this.container = container;
        this.fab       = null;
        this.popup     = null;
        this.isOpen    = false;
        this.isVisible = false;

        this._readConfig();
        this._build();
        this._bindEvents();
        this._applyDelay();
    }

    // -------------------------------------------------------------------------
    // Initialisation
    // -------------------------------------------------------------------------

    /**
     * Read all configuration from data-attributes on the container element.
     */
    _readConfig() {
        const d = this.container.dataset;

        this.phone             = d.phone             || '';
        this.prefilled         = d.prefilled          || '';
        this.position          = d.position           || 'right';
        this.delay             = parseInt( d.delay, 10 ) || 0;
        this.device            = d.device             || 'all';
        this.fabText           = ( d.fabText || '' ).trim();
        this.welcomeTitle      = d.welcomeTitle       || '';
        this.welcomeMessage    = d.welcomeMessage     || '';
        this.ctaText           = d.ctaText            || '';
        this.awayCtaText       = d.awayCtaText        || '';
        this.fabLabel          = d.fabLabel          || '';
        this.popupLabel        = d.popupLabel        || '';
        this.closeLabel        = d.closeLabel        || '';
        this.statusOnlineLabel = d.statusOnlineLabel || '';
        this.statusAwayLabel   = d.statusAwayLabel   || '';
        this.awayMessage       = d.awayMessage        || '';
        this.awayNextAvail     = d.awayNextAvailability || '';
        this.timezone          = d.timezone           || 'UTC';
        this.privacyNotice     = d.privacyNotice      || '';
        this.privacyLink       = d.privacyLink        || '';
        this.avatarImage       = d.avatarImage        || '';
        this.avatarAlt         = d.avatarAlt          || '';

        try {
            this.schedule  = JSON.parse( d.schedule  || '[]' );
        } catch ( e ) {
            this.schedule  = [];
        }

        try {
            this.holidays  = JSON.parse( d.holidays  || '[]' );
        } catch ( e ) {
            this.holidays  = [];
        }
    }

    /**
     * Build and inject the FAB and popup HTML into the container.
     */
    _build() {
        const isOnline = this._isOnline();

        // Floating Action Button.
        this.fab = document.createElement( 'button' );
        this.fab.className   = 'tm-whatsapp-fab';
        this.fab.type        = 'button';
        this.fab.setAttribute( 'aria-expanded', 'false' );
        if ( this.fabLabel ) {
            this.fab.setAttribute( 'aria-label', this.fabLabel );
        }
        this.fab.setAttribute( 'aria-controls', 'tm-whatsapp-popup' );
        this.fab.classList.add( this.fabText ? 'tm-whatsapp-fab--with-text' : 'tm-whatsapp-fab--icon-only' );
        this.fab.innerHTML   = this._fabHTML();

        // Popup.
        this.popup = document.createElement( 'div' );
        this.popup.className  = 'tm-whatsapp-popup';
        this.popup.id         = 'tm-whatsapp-popup';
        this.popup.setAttribute( 'role', 'complementary' );
        if ( this.popupLabel ) {
            this.popup.setAttribute( 'aria-label', this.popupLabel );
        }
        this.popup.setAttribute( 'aria-hidden', 'true' );
        this.popup.innerHTML  = this._popupHTML( isOnline );

        // Position modifier on the container.
        this.container.classList.add( 'tm-whatsapp-widget--' + this.position );

        this.container.appendChild( this.fab );
        this.container.appendChild( this.popup );
    }

    /**
     * Bind all event listeners using delegation.
     */
    _bindEvents() {
        // FAB toggle.
        this.fab.addEventListener( 'click', () => this.togglePopup() );

        // Keyboard: open/close with Enter or Space on FAB.
        this.fab.addEventListener( 'keydown', ( e ) => {
            if ( e.key === 'Enter' || e.key === ' ' ) {
                e.preventDefault();
                this.togglePopup();
            }
        } );

        // Close button inside popup.
        this.popup.addEventListener( 'click', ( e ) => {
            if ( e.target.closest( '.tm-whatsapp-popup-close' ) ) {
                this.closePopup();
            }
        } );

        // CTA button.
        this.popup.addEventListener( 'click', ( e ) => {
            const cta = e.target.closest( '.tm-whatsapp-popup-cta' );
            if ( cta ) {
                document.dispatchEvent( new CustomEvent( 'tm-whatsapp-chat' ) );
            }
        } );

        // Click outside popup and FAB → close.
        document.addEventListener( 'click', ( e ) => {
            if ( this.isOpen && ! this.container.contains( e.target ) ) {
                this.closePopup();
            }
        } );

        // Close with Escape key.
        document.addEventListener( 'keydown', ( e ) => {
            if ( e.key === 'Escape' && this.isOpen ) {
                this.closePopup();
                this.fab.focus();
            }
        } );

        window.addEventListener( 'resize', () => this._syncVisibility() );
    }

    /**
     * Apply the configured appearance delay before making the widget visible.
     */
    _applyDelay() {
        if ( this.delay > 0 ) {
            setTimeout( () => {
                this.isVisible = true;
                this._syncVisibility();
            }, this.delay * 1000 );
        } else {
            this.isVisible = true;
            this._syncVisibility();
        }
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Toggle the popup open/closed.
     */
    togglePopup() {
        this.isOpen ? this.closePopup() : this.openPopup();
    }

    /**
     * Open the welcome popup.
     */
    openPopup() {
        this.isOpen = true;
        this.popup.classList.add( 'tm-whatsapp-popup--open' );
        this.popup.setAttribute( 'aria-hidden', 'false' );
        this.fab.setAttribute( 'aria-expanded', 'true' );
        this.fab.classList.add( 'tm-whatsapp-fab--active' );

        // Move focus to close button for accessibility.
        const closeBtn = this.popup.querySelector( '.tm-whatsapp-popup-close' );
        if ( closeBtn ) {
            closeBtn.focus();
        }

        document.dispatchEvent( new CustomEvent( 'tm-whatsapp-open' ) );
    }

    /**
     * Close the welcome popup.
     */
    closePopup() {
        this.isOpen = false;
        this.popup.classList.remove( 'tm-whatsapp-popup--open' );
        this.popup.setAttribute( 'aria-hidden', 'true' );
        this.fab.setAttribute( 'aria-expanded', 'false' );
        this.fab.classList.remove( 'tm-whatsapp-fab--active' );

        document.dispatchEvent( new CustomEvent( 'tm-whatsapp-close' ) );
    }

    // -------------------------------------------------------------------------
    // Schedule / availability logic
    // -------------------------------------------------------------------------

    /**
     * Determine whether the team is currently available.
     *
     * Checks holidays first, then the weekly schedule.
     *
     * @returns {boolean}
     */
    _isOnline() {
        const now        = this._nowInTimezone();
        const todayDate  = now.date;   // 'YYYY-MM-DD'
        const todayDay   = now.day;    // 'monday', 'tuesday', …
        const currentTime = now.time;  // 'HH:MM'

        // Holiday check — away for the whole day.
        const isHoliday = this.holidays.some( ( h ) => h.date === todayDate );
        if ( isHoliday ) {
            return false;
        }

        // Weekly schedule check.
        const entry = this.schedule.find( ( s ) => s.day === todayDay );
        if ( ! entry || ! entry.is_open ) {
            return false;
        }

        return currentTime >= entry.open_time && currentTime < entry.close_time;
    }

    /**
     * Get current date/time components in the configured timezone.
     *
     * @returns {{ date: string, day: string, time: string }}
     */
    _nowInTimezone() {
        const now = new Date();

        const dateStr = new Intl.DateTimeFormat( 'en-CA', {
            timeZone : this.timezone,
            year     : 'numeric',
            month    : '2-digit',
            day      : '2-digit',
        } ).format( now ); // → 'YYYY-MM-DD'

        const dayStr = new Intl.DateTimeFormat( 'en-US', {
            timeZone : this.timezone,
            weekday  : 'long',
        } ).format( now ).toLowerCase(); // → 'monday', 'tuesday', …

        const timeStr = new Intl.DateTimeFormat( 'en-GB', {
            timeZone : this.timezone,
            hour     : '2-digit',
            minute   : '2-digit',
            hour12   : false,
        } ).format( now ); // → 'HH:MM'

        return { date: dateStr, day: dayStr, time: timeStr };
    }

    // -------------------------------------------------------------------------
    // Device visibility
    // -------------------------------------------------------------------------

    /**
     * Check if the widget should be visible on the current device.
     *
     * @returns {boolean}
     */
    _isDeviceVisible() {
        if ( this.device === 'all' ) {
            return true;
        }

        const isMobile = window.matchMedia( '(max-width: 767px)' ).matches;

        if ( this.device === 'mobile' && isMobile )  return true;
        if ( this.device === 'desktop' && ! isMobile ) return true;

        return false;
    }

    _syncVisibility() {
        const shouldShow = this.isVisible && this._isDeviceVisible();
        this.container.classList.toggle( 'tm-whatsapp-widget--visible', shouldShow );

        if ( ! shouldShow && this.isOpen ) {
            this.closePopup();
        }
    }

    // -------------------------------------------------------------------------
    // HTML builders
    // -------------------------------------------------------------------------

    /**
     * Build the full popup inner HTML.
     *
     * @param {boolean} isOnline
     * @returns {string}
     */
    _popupHTML( isOnline ) {
        const closeLabel  = this.closeLabel || '';
        const ctaText     = isOnline ? this.ctaText : ( this.awayCtaText || this.ctaText );
        const statusLabel = isOnline ? this.statusOnlineLabel : this.statusAwayLabel;
        const message     = isOnline ? this.welcomeMessage : this.awayMessage;
        const waHref      = 'https://wa.me/' + this.phone + '?text=' + encodeURIComponent( this.prefilled );

        const nextAvail = ( ! isOnline && this.awayNextAvail )
            ? '<p class="tm-whatsapp-popup-next-avail">' + this._escapeHTML( this.awayNextAvail ) + '</p>'
            : '';

        const privacy = this.privacyNotice
            ? '<p class="tm-whatsapp-popup-privacy">'
                + ( this.privacyLink
                    ? '<a href="' + this._escapeHTML( this.privacyLink ) + '" target="_blank" rel="noopener noreferrer">'
                        + this._escapeHTML( this.privacyNotice )
                        + '</a>'
                    : this._escapeHTML( this.privacyNotice ) )
                + '</p>'
            : '';

        return ''
            + '<div class="tm-whatsapp-popup-header">'
            +   '<div class="tm-whatsapp-popup-header-info">'
            +     this._avatarHTML()
            +     '<div>'
            +       '<p class="tm-whatsapp-popup-title">' + this._escapeHTML( this.welcomeTitle ) + '</p>'
            +       '<span class="tm-whatsapp-popup-status tm-whatsapp-popup-status--' + ( isOnline ? 'online' : 'away' ) + '">'
            +         '<span class="tm-whatsapp-popup-status-dot" aria-hidden="true"></span>'
            +         this._escapeHTML( statusLabel )
            +       '</span>'
            +     '</div>'
            +   '</div>'
            +   '<button class="tm-whatsapp-popup-close" type="button" aria-label="' + this._escapeHTML( closeLabel ) + '">'
            +     '&times;'
            +   '</button>'
            + '</div>'
            + '<div class="tm-whatsapp-popup-body">'
            +   '<div class="tm-whatsapp-popup-bubble">'
            +     '<p>' + this._escapeHTML( message ) + '</p>'
            +     nextAvail
            +   '</div>'
            + '</div>'
            + '<div class="tm-whatsapp-popup-footer">'
            +   '<a class="tm-whatsapp-popup-cta" href="' + waHref + '" target="_blank" rel="noopener noreferrer">'
            +     this._iconSVG( 20 )
            +     this._escapeHTML( ctaText )
            +   '</a>'
            +   privacy
            + '</div>';
    }

    /**
     * Build the floating action button HTML.
     *
     * @returns {string}
     */
    _fabHTML() {
        if ( ! this.fabText ) {
            return this._iconSVG( 36 );
        }

        const text = this.fabText
            ? '<span class="tm-whatsapp-fab__text">' + this._escapeHTML( this.fabText ) + '</span>'
            : '';

        return ''
            + text
            + '<span class="tm-whatsapp-fab__icon" aria-hidden="true">'
            +   this._iconSVG( 20 )
            + '</span>';
    }

    /**
     * WhatsApp logo SVG.
     *
     * @param {number} size
     * @returns {string}
     */
    _iconSVG( size ) {
        size = size || 28;
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">'
            + '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>'
            + '<path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.554 4.122 1.523 5.855L.057 23.882a.5.5 0 0 0 .61.61l6.101-1.459A11.945 11.945 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.907 0-3.688-.526-5.208-1.437l-.372-.22-3.863.924.944-3.776-.243-.389A9.953 9.953 0 0 1 2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>'
            + '</svg>';
    }

    /**
     * Generic avatar / brand avatar SVG for the popup header.
     *
     * @returns {string}
     */
    _avatarHTML() {
        if ( this.avatarImage ) {
            return '<div class="tm-whatsapp-popup-avatar tm-whatsapp-popup-avatar--logo" aria-hidden="true">'
                + '<img src="' + this._escapeHTML( this.avatarImage ) + '" alt="' + this._escapeHTML( this.avatarAlt ) + '">'
                + '</div>';
        }

        return '<div class="tm-whatsapp-popup-avatar" aria-hidden="true">'
            + '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="40" height="40">'
            + '<path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>'
            + '</svg>'
            + '</div>';
    }

    /**
     * Escape HTML special characters to prevent XSS.
     *
     * Data comes from WordPress ACF options (trusted admin input), but we
     * escape anyway as a defensive measure.
     *
     * @param {string} str
     * @returns {string}
     */
    _escapeHTML( str ) {
        return String( str )
            .replace( /&/g,  '&amp;' )
            .replace( /</g,  '&lt;' )
            .replace( />/g,  '&gt;' )
            .replace( /"/g,  '&quot;' )
            .replace( /'/g,  '&#039;' );
    }
}

// Initialise when DOM is ready.
document.addEventListener( 'DOMContentLoaded', () => {
    const container = document.querySelector( '.tm-whatsapp-widget' );
    if ( container ) {
        window.tmWhatsapp = new TMWhatsapp( container );
    }
} );
