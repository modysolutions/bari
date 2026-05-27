/**
 * Toggle favourite post
 */
document.addEventListener("DOMContentLoaded", function () {
    document.body.addEventListener("click", function (e) {
        const icon = e.target.closest(".tm-favourite-icon");

        if (!icon) return;

        const postId = icon.getAttribute("data-post-id");

        fetch(tmFavourites.ajax_url, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                action: "toggle_favourite",
                nonce: tmFavourites.nonce,
                post_id: postId,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const isFavourite = data.data.is_favourite;
                    const wrapper = icon.closest(".tm-favourite-icon-wrapper");
                    const tooltip = wrapper.querySelector(".tm-favourite-tooltip");

                    if (isFavourite) {
                        // If it is a favorite, add a tooltip if it doesn't exist.
                        if (!tooltip) {
                            const newTooltip = document.createElement("a");
                            newTooltip.className = "tm-favourite-tooltip";
                            newTooltip.href = tmFavourites.my_favourites_url; // Use the URL from ACF Options.
                            newTooltip.textContent = "Go to My Favourites";
                            wrapper.appendChild(newTooltip);
                        }
                    } else {
                        // If it is not a favorite, remove the tooltip if it exists.
                        if (tooltip) {
                            tooltip.remove();
                        }
                    }

                    // Update the icon.
                    const svg = icon.querySelector("svg");
                    if (isFavourite) {
                        svg.outerHTML =
                            '<svg class="tm-heart tm-heart-filled" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M200-120v-640q0-33 23.5-56.5T280-840h400q33 0 56.5 23.5T760-760v640L480-240 200-120Z"/></svg>';
                    } else {
                        svg.outerHTML =
                            '<svg class="tm-heart tm-heart-outline" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M200-120v-640q0-33 23.5-56.5T280-840h400q33 0 56.5 23.5T760-760v640L480-240 200-120Zm80-122 200-86 200 86v-518H280v518Zm0-518h400-400Z"/></svg>';
                    }
                } else {
                    console.error(data);
                }
            })
            .catch((error) => console.error("Error:", error));
    });
});

/**
 * Filter favourites
 */
document.addEventListener("DOMContentLoaded", function () {
    const checkboxes = document.querySelectorAll(".filter-checkbox");
    const cards = document.querySelectorAll(".tm-post-card");

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener("change", function () {
            filterFavourites();
        });
    });

    function filterFavourites() {
        let activeFilters = {};

        // Group the selected filters by taxonomy
        checkboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                const taxonomy = checkbox.getAttribute("data-taxonomy");
                const value = checkbox.value;

                if (!activeFilters[taxonomy]) {
                    activeFilters[taxonomy] = [];
                }
                activeFilters[taxonomy].push(value);
            }
        });

        // Show/hide cards based on active filters
        cards.forEach((card) => {
            let show = true;

            for (const taxonomy in activeFilters) {
                const filters = activeFilters[taxonomy];
                const cardValues = card.getAttribute(`data-${taxonomy}`);

                if (!cardValues || !filters.some((filter) => cardValues.includes(filter))) {
                    show = false;
                    break;
                }
            }

            card.style.display = show ? "block" : "none";
        });
    }
});

/**
 * Remove favourite
 */
document.addEventListener('DOMContentLoaded', function () {
    document.body.addEventListener('click', function (event) {
        let button = event.target.closest('.tm-remove-favourite');
        if (!button) return;

        event.preventDefault();

        let postId = button.dataset.postId;

        button.disabled = true;

        fetch(tmFavourites.ajax_url, {
            method: 'POST',
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                action: "tamarind_remove_favourite",
                nonce: tmFavourites.nonce,
                post_id: postId,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let card = button.closest('.tm-post-card');
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 300);
            } else {
                alert('Error removing favourite.');
            }
        })
        .catch(() => alert('Error processing request.'))
        .finally(() => button.disabled = false);
    });
});
