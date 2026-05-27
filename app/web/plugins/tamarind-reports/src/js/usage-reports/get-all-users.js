import apiFetch from "@wordpress/api-fetch";
import {__} from '@wordpress/i18n';
import {getFilterValue, setUpFilters, updatePagination, updateTotal} from "./functions";
import UserRow from "./components/user-row";
import NoResultsRow from "./components/no-results-row";
import UserRowDetail from "./components/user-row-detail";

const getAllUsers = async (view) => {
    const form = document.querySelector('.usage-form');
    const filtersString = setUpFilters(form, view, true);
    const filters = setUpFilters(form, view);
    const page = filters.get('report_page');
    const totalPagesSelect = document.querySelector('[name="report_page"]');
    const downloadButton = document.querySelector('.download-report');
    let tbody = document.querySelector('.usage-report-table > tbody');
    const getColspanByView = () => {
        const selector = view === 'detail' ? '#tamarind-report-head-log th' : '#tamarind-reports-head th';
        const count = document.querySelectorAll(selector).length;
        return count > 0 ? count : 10;
    };
    const colSpan = getColspanByView();

    const newPath = `/usage-report/?${filtersString}`;
    history.pushState(null, '', newPath);

    if (view === 'single' && !filtersString.includes('user_id')) {
        tbody.innerHTML = '';
        const noResultsRow = document.createElement('tr');
        noResultsRow.innerHTML = NoResultsRow(colSpan, __('Please, search a user to start', 'tamarind-reports'));
        tbody.appendChild(noResultsRow);
        return;
    }

    downloadButton.setAttribute('href', `/usage-report/?${filtersString}&export=csv`);

    return await apiFetch({
        path: `/tamarind/v2/usage-report/${view}?${filtersString}`,
        method: 'GET'
    })
        .then(data => {
            const {is_admin: isAdmin, users, total_pages: totalPages, total} = data;

            if (total === 0) {
                const noResultsRow = document.createElement('tr');
                noResultsRow.innerHTML = NoResultsRow(colSpan, __('No results found', 'tamarind-reports'));
                tbody.innerHTML = noResultsRow.outerHTML;
                updatePagination(totalPages, page);
                updateTotal(total);
                return;
            }
            let rows = '';
            users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = view === 'detail' ? UserRowDetail(user, isAdmin) : UserRow(user, isAdmin);
                rows += row.outerHTML;
            });
            tbody.innerHTML = rows;

            if (totalPages >= 1) {
                totalPagesSelect.innerHTML = '';
                for (let i = 1; i <= totalPages; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.selected = i === parseInt(page, 10);
                    option.innerHTML = i;
                    totalPagesSelect.appendChild(option);
                }

                totalPagesSelect.value = page;
                return {totalPages, total, page};
            }
        })
        .catch(error => {
            console.error('There was a problem with your fetch operation:', error);
            return error;
        });
}

export default getAllUsers;
