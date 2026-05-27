/**
 * Upcoming Events Module - Main JavaScript File
 * Handles the display and navigation of events by month
 */

document.addEventListener('DOMContentLoaded', function () {
    // State variables
    let currentMonth = parseInt(eventsModuleData.defaultMonth);
    let currentYear = parseInt(eventsModuleData.defaultYear);
    let availableMonths = [];
    const eventsModule = document.querySelector('.events-module');

    // Initialize the module if the container exists
    if (eventsModule) {
        initEventsModule();
        setupEventModalButtons();
    }

    /**
     * Initializes the events module
     * Sets up the initial state and event listeners
     */
    function initEventsModule() {
        updateMonthDisplay();
        
        // Load available months first, then load events
        fetchAvailableMonths().then(() => {
            loadEvents(currentYear, currentMonth);
        });
        
        setupNavigation();
    }

     /**
     * Sets up event listeners for modal buttons
     */
     function setupEventModalButtons() {
        document.addEventListener('click', function(event) {
            
            const button = event.target.closest('button[data-event]');
            if (!button) return;

            const eventValue = button.getAttribute('data-event');
            
            // Buscar el formulario dentro del modal #event-meetup-form
            const modalForm = document.querySelector('#event-meetup-form');
            
            if (modalForm) {
                const hiddenField = modalForm.querySelector('.tm-hidden-event_name input[type="hidden"]');             
                
                if (hiddenField) {
                    hiddenField.value = eventValue;
                }
            }
        });
    }

    /**
     * Fetches available months with events from the server
     * @async
     */
    async function fetchAvailableMonths() {
        try {
            const params = new URLSearchParams();
            params.append('action', 'get_available_months_with_events');
            params.append('security', eventsModuleData.security);

            const response = await fetch(eventsModuleData.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: params
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                availableMonths = data.data;
                updateNavigation();
            }
        } catch (error) {
            console.error('Error fetching available months:', error);
        }
    }

    /**
     * Updates the navigation buttons state
     * Disables buttons when there are no more months to navigate
     */
    function updateNavigation() {
        const prevBtn = eventsModule.querySelector('.prev-month');
        const nextBtn = eventsModule.querySelector('.next-month');
        
        // Convert current month to comparable format (YYYYMM)
        const currentDateNum = parseInt(`${currentYear}${currentMonth.toString().padStart(2, '0')}`);
        
        // Get all available months as numbers
        const availableDates = availableMonths.map(m => 
            parseInt(`${m.year}${m.month.padStart(2, '0')}`)
        ).sort((a, b) => a - b);
        
        // Find closest months
        const previousMonths = availableDates.filter(d => d < currentDateNum);
        const nextMonths = availableDates.filter(d => d > currentDateNum);
        
        // Update buttons
        prevBtn.classList.toggle('disabled', previousMonths.length === 0);
        nextBtn.classList.toggle('disabled', nextMonths.length === 0);
    }
    
    /**
     * Sets up event listeners for navigation buttons
     */
    function setupNavigation() {
        const prevBtn = eventsModule.querySelector('.prev-month');
        const nextBtn = eventsModule.querySelector('.next-month');

        prevBtn.addEventListener('click', () => {
            if (prevBtn.classList.contains('disabled')) return;
            navigateToPreviousMonth();
        });

        nextBtn.addEventListener('click', () => {
            if (nextBtn.classList.contains('disabled')) return;
            navigateToNextMonth();
        });
    }

    /**
     * Navigates to the previous month
     * Handles year transition when going from January to December
     */
    function navigateToPreviousMonth() {
        const currentDateNum = parseInt(`${currentYear}${currentMonth.toString().padStart(2, '0')}`);
        const availableDates = availableMonths.map(m => 
            parseInt(`${m.year}${m.month.padStart(2, '0')}`)
        ).sort((a, b) => a - b);
        
        // Find closest previous month with events
        const previousMonths = availableDates.filter(d => d < currentDateNum);
        if (previousMonths.length > 0) {
            const prevDate = Math.max(...previousMonths);
            currentYear = Math.floor(prevDate / 100);
            currentMonth = prevDate % 100;
            loadEvents(currentYear, currentMonth);
        }
    }

    /**
     * Navigates to the next month
     * Handles year transition when going from December to January
     */
    function navigateToNextMonth() {
        const currentDateNum = parseInt(`${currentYear}${currentMonth.toString().padStart(2, '0')}`);
        const availableDates = availableMonths.map(m => 
            parseInt(`${m.year}${m.month.padStart(2, '0')}`)
        ).sort((a, b) => a - b);
        
        // Find closest next month with events
        const nextMonths = availableDates.filter(d => d > currentDateNum);
        if (nextMonths.length > 0) {
            const nextDate = Math.min(...nextMonths);
            currentYear = Math.floor(nextDate / 100);
            currentMonth = nextDate % 100;
            loadEvents(currentYear, currentMonth);
        }
    }

    /**
     * Loads events for a specific month via AJAX
     * @async
     * @param {number} year - The year to load events for
     * @param {number} month - The month to load events for (1-12)
     */
    async function loadEvents(year, month) {
        const monthStr = month.toString().padStart(2, '0');
        const container = eventsModule.querySelector('.events-list-container');
        const loading = eventsModule.querySelector('.events-loading');

        loading.style.display = 'block';
        container.innerHTML = '';

        try {
            const params = new URLSearchParams();
            params.append('action', 'get_events_by_month');
            params.append('year', year);
            params.append('month', monthStr);
            params.append('security', eventsModuleData.security);
            params.append('limit', window.eventsModuleConfig?.eventsLimit || -1);
            params.append('link_events_page', window.eventsModuleConfig?.linkEventsPage || false);

            const response = await fetch(eventsModuleData.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: params
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            loading.style.display = 'none';

            if (data.success) {
                updateMonthDisplay(data.data.month_name);
                updateNavigation();

                if (data.data.events?.length > 0) {
                    renderEvents(
                        data.data.events, 
                        data.data.has_more, 
                        data.data.more_count, 
                        data.data.archive_link,
                    );
                } else {
                    container.innerHTML = '<p class="no-events">No events scheduled for this month.</p>';
                }
            } else {
                showErrorMessage('Error loading events');
            }
        } catch (error) {
            loading.style.display = 'none';
            showErrorMessage('Connection error');
            console.error('Error:', error);
        }
    }

    /**
     * Renders the events list in the container
     * @param {Array} events - Array of event objects to render
     */
    function renderEvents(events, hasMore, moreCount, archiveLink) {
        const container = eventsModule.querySelector('.events-list-container');
        const eventsList = document.createElement('ul');
        eventsList.className = 'events-list';

        events.forEach(event => {
            const eventItem = createEventElement(event);
            eventsList.appendChild(eventItem);
        });

        // Show button only if there are more events and a link is configured
        if (hasMore && archiveLink) {
            const showMoreItem = document.createElement('li');
            showMoreItem.className = 'show-more-events';
            showMoreItem.innerHTML = `
                <a href="${archiveLink}" class="show-more-link">
                    Show more events (+${moreCount})
                </a>
            `;
            eventsList.appendChild(showMoreItem);
        }

        container.innerHTML = '';
        container.appendChild(eventsList);
    }

    /**
     * Creates a DOM element for a single event
     * @param {Object} event - Event data object
     * @returns {HTMLElement} The created event element
     */
    function createEventElement(event) {
        const startDate = new Date(event.start);
        const endDate = event.end ? new Date(event.end) : null;

        const eventItem = document.createElement('li');
        eventItem.className = `event-item ${event.feat ? 'featured' : ''}`;

        let eventImage = '';
        if (event.img) {
            eventImage = `
                <div class="event-image">
                    <img src="${event.img}" alt="${event.title}" loading="lazy">
                </div>
            `;
        }

        // Format the date range
        const formattedDate = formatDateRange(startDate, endDate);

        let eventLink = '';
        if (event.url) {
            eventLink = `
                <div class="event-website"><a href="${event.url}" class="event-link" target="_blank" rel="noopener noreferrer">Event website</a></div>
            `;
        }

        let eventModal = '';
        if ( event.feat && event.btn != '' ) {
            eventModal = `
            <div class="event-modal"><button data-event="${event.title}" data-tm-modal-target="#event-meetup-form" class="tm-modal-trigger event-link event-link--modal" rel="noopener">${event.btn}</button></div>`;
        }

        eventItem.innerHTML = `
            ${eventImage}
            <div class="event-details">
                <h3>${event.title}</h3>
                <p class="event-date">${formattedDate}</p>
                <p class="event-place">${event.place || ''}</p>
            </div>
            ${eventModal}
            ${eventLink}
        `;

        return eventItem;
    }

    /**
     * Formats a date range with English ordinal indicators
     * @param {Date} startDate - Start date object
     * @param {Date} endDate - End date object (optional)
     * @returns {string} Formatted date range string
     */
    function formatDateRange(startDate, endDate) {
        const startDay = getOrdinalSuffix(startDate.getDate());
        const startMonth = startDate.toLocaleDateString('en-US', { month: 'short' });
        
        // If no end date or same day, return single date
        if (!endDate || isSameDay(startDate, endDate)) {
            return `${startDay} ${startMonth}`;
        }
        
        // If same month, show "3rd-4th July"
        if (startDate.getMonth() === endDate.getMonth() && 
            startDate.getFullYear() === endDate.getFullYear()) {
            const endDay = getOrdinalSuffix(endDate.getDate());
            return `${startDay}-${endDay} ${startMonth}`;
        }
        
        // Different months: "3rd July - 4th Aug"
        const endDay = getOrdinalSuffix(endDate.getDate());
        const endMonth = endDate.toLocaleDateString('en-US', { month: 'short' });
        return `${startDay} ${startMonth} - ${endDay} ${endMonth}`;
    }

    /**
     * Gets the ordinal suffix for a day number
     * @param {number} day - The day of the month
     * @returns {string} Day with ordinal suffix (e.g. "3rd")
     */
    function getOrdinalSuffix(day) {
        if (day > 3 && day < 21) return `${day}th`; // 11th, 12th, 13th exceptions
        
        switch (day % 10) {
            case 1:  return `${day}st`;
            case 2:  return `${day}nd`;
            case 3:  return `${day}rd`;
            default: return `${day}th`;
        }
    }

    /**
     * Checks if two dates are the same day
     * @param {Date} date1 - First date
     * @param {Date} date2 - Second date
     * @returns {boolean} True if same day
     */
    function isSameDay(date1, date2) {
        return date1.getDate() === date2.getDate() && 
            date1.getMonth() === date2.getMonth() && 
            date1.getFullYear() === date2.getFullYear();
    }

    /**
     * Updates the month display in the header
     * @param {string} [monthName=null] - Optional month name to display
     */
    function updateMonthDisplay(monthName = null) {
        const monthDisplay = eventsModule.querySelector('.current-month');
        monthDisplay.textContent = monthName || eventsModuleData.defaultMonthName;
    }

    /**
     * Displays an error message in the events container
     * @param {string} message - Error message to display
     */
    function showErrorMessage(message) {
        const container = eventsModule.querySelector('.events-list-container');
        container.innerHTML = `<p class="error-message">${message}</p>`;
    }
});