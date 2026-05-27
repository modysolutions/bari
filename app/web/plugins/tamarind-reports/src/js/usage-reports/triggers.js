import handleReportTypeChange from './report-type-change';
import domReady from "@wordpress/dom-ready";
import {
    handleResponse,
    initializePagination,
    isPaginationInitialized,
    updatePagination,
    updateTotal
} from "./functions";

const clearUserFilters = () => {
    const userId = document.querySelector('[name="user_id"]');
    const userEmail = document.querySelector('[name="user_email"]');

    if (userId) {
        userId.value = '';
    }
    if (userEmail) {
        userEmail.value = '';
    }
};

const clearClientFilter = () => {
    const client = document.querySelector('[name="client"]');
    if (client) {
        client.value = '';
    }
};

const clearIncompatibleFiltersForView = (view) => {
    if (view === 'all') {
        clearUserFilters();
        return;
    }

    if (view === 'single') {
        clearClientFilter();
    }
};

domReady(() => {
    const form = document.querySelector('.tamarind-report-usage-form');
    const radioButtons = document.querySelectorAll('.tm-btn > [name="report-type"]');
    const tamarindReportFilters = document.querySelectorAll('.tamarind-report-filter');
    const url = new URL(window.location.href);
    const params = url.searchParams;
    const view = params.get('view') ?? 'all';

    if(form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            await handleReportTypeChange(document.getElementById('tm-reports-view').value)
                .then((data) => handleResponse(data));
        });
    }

    if(radioButtons.length) {
        radioButtons.forEach(radio => {
            radio.addEventListener('change', async (event) => {
                const parentLabel = event.target.closest('label');
                radioButtons.forEach(btn => {
                    const label = btn.closest('label');
                    label.classList.remove('btn-default');
                    label.classList.add('btn-transparent');
                });
                parentLabel.classList.remove('btn-transparent');
                parentLabel.classList.add('btn-default');
                const view = event.target.value;
                clearIncompatibleFiltersForView(view);
                document.getElementById('tm-reports-view').value = view;
                await handleReportTypeChange(view)
                    .then((data) => handleResponse(data));
            });
        });

        const selected = Array.from(radioButtons).filter(btn => btn.value === view);
        if(selected.length) {
            selected[0].checked = true;
            selected[0].dispatchEvent(new Event('change'));
        }
    }

    if(tamarindReportFilters.length) {
        tamarindReportFilters.forEach(filter => {
            filter.addEventListener('change', async (event) => {
                const view = document.getElementById('tm-reports-view').value;
                if (event.target.name === 'client') {
                    clearUserFilters();
                }
                if(event.target.name !== 'report_page') {
                    const reportPage = document.querySelector(`[name="report_page"]`);
                    reportPage.value = 1;
                    reportPage.querySelector('option[value="1"]').selected = true;
                }
                await handleReportTypeChange(view)
                    .then((data) => handleResponse(data));
            });
        });
    }
})
