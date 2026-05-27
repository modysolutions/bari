import getAllUsers from "./get-all-users";
import UserRowDetail from "./components/user-row-detail";
import UserRow from "./components/user-row";

const handleReportTypeChange = async (view, callback = () => {}) => {
    document.querySelectorAll('.dynamic-label').forEach((item) => item.style.display = 'none');
    document.querySelectorAll('.dynamic-block').forEach((item) => item.style.display = 'none');
    document.querySelectorAll(`.${view}.dynamic-label`).forEach((item) => item.style.display = 'inline-block');
    document.querySelectorAll(`.${view}.dynamic-block`).forEach((item) => item.style.display = 'contents');
    const tbody = document.querySelector('.usage-report-table tbody');
    if(tbody) {
        const isAdminFE = !!document.querySelector('.col-client');
        const row = document.createElement('tr');
        row.innerHTML = view === 'detail' ?
            UserRowDetail({}, isAdminFE, true) :
            UserRow({}, isAdminFE, true);
        tbody.innerHTML = '';
        tbody.appendChild(row);
    }
    return await getAllUsers(view).then((data) => {
        if(callback && typeof callback === 'function' && data && data.length > 0) {
            callback(data);
        }
        return data;
    });
}

export default handleReportTypeChange;