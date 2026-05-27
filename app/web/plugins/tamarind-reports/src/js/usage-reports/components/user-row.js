import badges from "./badges";
import LoadingBar from "./loading-bar";

const UserRow = (user, isAdmin = false, loading = false) => `
    ${isAdmin ? `<td class="col-client">${!loading ? user?.client : LoadingBar()}</td>` : ''}
    <td class="col-email">${!loading ? user?.email : LoadingBar()}</td>
    <td class="col-num col-pv">${!loading ? user?.page_views : LoadingBar()}</td>
    <td class="col-num col-dl">${!loading ? user?.downloads_count : LoadingBar()}</td>
    <td class="col-topics col-num">${!loading ? user?.topics_count : LoadingBar()}</td>
    <td>
        <div class="top-five-topics">
            ${!loading ? badges(user?.top_topics, 1) : LoadingBar()}
        </div>
    </td>
    <td>
        <div class="">
            ${!loading ? badges(user?.geos, 1) : LoadingBar()}
        </div>
    </td>
    <td>
        <div class="">
            ${!loading ? badges(user?.top_geos, 1) : LoadingBar()}
        </div>
    </td>
    <td>
        ${!loading ? `
            <div class="pct-meter">
                <span class="pct-stack">
                    <span class="pct-fill pct-reg" style="width: ${user?.regulatory_percent}%"></span>
                    <span class="pct-fill pct-mkt" style="width: ${user?.market_percent}%"></span>
                </span>
            </div>
            <span class="pct-val">${user?.regulatory_percent}% / ${user?.market_percent}%</span>
        ` : LoadingBar()}   
    </td>
`;

export default UserRow;