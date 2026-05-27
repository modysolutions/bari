import {sprintf, __} from '@wordpress/i18n';
import handleReportTypeChange from "./report-type-change";

export const setUpFilters = (form, view, string = false) => {
    if (!form) {
        return;
    }

    const isAllOrDetail = view === 'all' || view === 'detail';
    const isSingleOrDetail = view === 'single' || view === 'detail';

    const from = form?.querySelector('[name="from"]')?.value;
    const to = form?.querySelector('[name="to"]')?.value;
    const client = form?.querySelector('[name="client"]')?.value;
    const perPage = form?.querySelector('[name="per_page"]')?.value;
    const paged = form?.querySelector('[name="report_page"]')?.value;
    const includeEmpty = form?.querySelector('[name="include_empty"]')?.checked ? form?.querySelector('[name="include_empty"]')?.value : '';
    const userId = form?.querySelector('[name="user_id"]')?.value;
    const userEmail = form?.querySelector('[name="user_email"]')?.value;
    const subscriptionPlan = form?.querySelector('[name="subscription_plan"]')?.value;
    const contentType = form?.querySelector('[name="content_type"]')?.value;
    const subcontentType = form?.querySelector('[name="subcontent_type"]')?.value;
    const hasDownload = form?.querySelector('[name="has_download"]')?.value;
    const hasFavourite = form?.querySelector('[name="has_favourite"]')?.value;
    const run = form?.querySelector('[name="run"]')?.value;

    const params = new URLSearchParams();
    params.append('view', view);
    params.append('from', from);
    params.append('to', to);
    params.append('run', run);

    if (isAllOrDetail && client) {
        params.append('client', client);
    }
    if (isAllOrDetail && perPage) {
        params.append('per_page', perPage);
    }
    if (isAllOrDetail && paged) {
        params.append('report_page', paged);
    }
    if (isSingleOrDetail && userId) {
        params.append('user_id', userId);
    }
    if (isSingleOrDetail && userEmail) {
        params.append('user_email', userEmail);
    }
    if (isAllOrDetail && includeEmpty) {
        params.append('include_empty', includeEmpty);
    }
    if (view === 'detail' && subscriptionPlan) {
        params.append('subscription_plan', subscriptionPlan);
    }
    if (view === 'detail' && contentType) {
        params.append('content_type', contentType);
    }
    if (view === 'detail' && subcontentType) {
        params.append('subcontent_type', subcontentType);
    }
    if (view === 'detail' && hasDownload) {
        params.append('has_download', hasDownload);
    }
    if (view === 'detail' && hasFavourite) {
        params.append('has_favourite', hasFavourite);
    }

    if (string) {
        return params.toString();
    }
    return params;
}

export const getFilterValue = (name) => {
    const filter = document.querySelector(`[name="${name}"]`);
    return filter.value;
}

export const getMinMaxFromDropdown = (selector) => {
    const selectElement = document.querySelector(selector);

    if (!selectElement) {
        return null;
    }

    const values = Array.from(selectElement.options).map(option => parseInt(option.value, 10));
    const numericValues = values.filter(value => !isNaN(value));

    if (numericValues.length === 0) {
        return null;
    }

    const minValue = Math.min(...numericValues);
    const maxValue = Math.max(...numericValues);

    return {
        min: minValue,
        max: maxValue
    };
}

export const initializePagination = () => {
    const paginationContainer = document.querySelector('.tamarind-report-pagination');
    if (paginationContainer) {
        const nextButton = paginationContainer.querySelector('.next-button'),
            prevButton = paginationContainer.querySelector('.prev-button');
        nextButton.addEventListener('click', handlePaginationButton);
        prevButton.addEventListener('click', handlePaginationButton)
    }
}

export const updatePagination = (total, page) => {
    const paginationContainer = document.querySelector('.tamarind-report-pagination');
    paginationContainer.querySelector('.pagination-info').innerHTML = sprintf(
        __('Page %s of %s'),
        parseInt(page, 10),
        parseInt(total, 10)
    );
}

export const updateTotal = (total) => {
    const paginationContainer = document.querySelector('.tamarind-report-pagination');
    paginationContainer.querySelector('.usage-pagination-count').innerHTML = sprintf(
        __('%s results'),
        parseInt(total, 10)
    );
    paginationContainer.dataset.initialize = '1';
}

export const isPaginationInitialized = () => {
    const paginationContainer = document.querySelector('.tamarind-report-pagination');
    return paginationContainer.dataset.initialize === '1';
}
const handlePaginationButton = (event) => {
    event.preventDefault();
    const {min, max} = getMinMaxFromDropdown('[name="report_page"]');
    const reportPage = document.querySelector(`[name="report_page"]`);
    if (!reportPage) {
        return;
    }
    let nextPage;
    if (event.target.classList.contains('next-button')) {
        nextPage = parseInt(reportPage.value) + 1;
        if (nextPage > max) {
            return;
        }
    } else if (event.target.classList.contains('prev-button')) {
        nextPage = parseInt(reportPage.value) - 1;
        if (nextPage < min) {
            return;
        }
    }
    reportPage.value = nextPage;
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
    handleReportTypeChange(document.getElementById('tm-reports-view').value)
        .then((data) => {
            if(data) {
                const {totalPages, total, page} = data;
                if(!isPaginationInitialized()) {
                    initializePagination();
                }
                updatePagination(totalPages, page);
                updateTotal(total);
            }
        });
}

export const handleResponse = (data) => {
    if(data) {
        const {totalPages, total, page} = data;
        if (!isPaginationInitialized()) {
            initializePagination();
        }
        updatePagination(totalPages, page);
        updateTotal(total);
    }
}
