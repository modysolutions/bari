import badges from "./badges";
import LoadingBar from "./loading-bar";

const UserRowDetail = (user, isAdmin = false, loading = false) => {
    const renderUrlCell = (fullUrl) => {
        let urlHtml = '';
        if (fullUrl) {
            const max = 40;
            const short = fullUrl.substring(0, max);
            const needsMore = fullUrl.length > max;

            urlHtml = `
                <span class="url-short">${short}${needsMore ? '...' : ''}</span>
            `;
            if (needsMore) {
                urlHtml += `
                    <details class="usage-more">
                        <summary>Show full</summary>
                        <div class="usage-report-full-url">
                            <a href="${fullUrl}" target="_blank" rel="noopener noreferrer">${fullUrl}</a>
                        </div>
                    </details>
                `;
            }
        }
        return urlHtml;
    };

    const renderBadges = (all, length = 1) => {
        return badges(all, length);
    };

    return `
        <tr>
            <td class="col-date">${!loading ? user?.date : LoadingBar()}</td>
            <td class="col-email-sm">${!loading ? user?.email : LoadingBar()}</td>
            <td class="col-user-status">${!loading ? user?.user_status : LoadingBar()}</td>
            <td class="col-client">${!loading ? user?.company : LoadingBar()}</td>
            <td class="col-client-status">${!loading ? user?.client_status : LoadingBar()}</td>
            <td class="col-plan">${!loading ? user?.subscription_plan : LoadingBar()}</td>
            <td class="col-num col-client-users">${!loading ? user?.client_users_count : LoadingBar()}</td>
            <td class="col-title">${!loading ? user?.title : LoadingBar()}</td>
            <td class="col-num">${!loading ? user?.post_id : LoadingBar()}</td>
            <td class="col-event-type">${!loading ? user?.event_type : LoadingBar()}</td>
            <td class="col-url">
                ${!loading ? renderUrlCell(user?.download_url) : LoadingBar()}
            </td>
            <td>${!loading ? user?.download_type : LoadingBar()}</td>
            <td class="col-favourites">${!loading ? user?.favourites : LoadingBar()}</td>
            <td class="col-ctypes">${!loading ? user?.content_types : LoadingBar()}</td>
            <td class="col-subctypes">${!loading ? user?.subcontent_types : LoadingBar()}</td>
            <td class="col-geos-d">
                ${!loading ? renderBadges(user?.geographies) : LoadingBar() }
            </td>
            <td class="col-topics-d">
                ${!loading ? renderBadges(user?.topics) : LoadingBar()}
            </td>
            <td class="col-author">${!loading ? user?.author : LoadingBar()}</td>
            <td>${!loading ? user?.publication_date : LoadingBar()}</td>
            <td>${!loading ? user?.last_login : LoadingBar()}</td>
            <td>${!loading ? user?.client_creation_date : LoadingBar()}</td>
        </tr>
    `;
};

export default UserRowDetail;
