import { sprintf, __ } from '@wordpress/i18n';
const badges = (all, length = 10) => {
    const moreText = (count) => sprintf(__('+%d more', 'tamarind-reports'), count);

    if(typeof all === 'undefined' || all.length === 0) return '';
    all = all?.split(',').map(g => g.trim());
    const first = all?.slice(0, length);
    const rest = all?.slice(length);

    let html = `<div class="usage-badges">`;

    first.forEach(g => {
        html += `<span class="usage-badge">${g}</span>`;
    });

    html += `</div>`;

    if (rest.length > 0) {
        html += `
      <details class="usage-more">
        <summary>
          ${moreText(rest.length)}
        </summary>
        <div class="usage-badges">
          ${rest.map(g => `<span class="usage-badge">${g}</span>`).join('')}
        </div>
      </details>
    `;
    }

    return html;
}

export default badges;