document.addEventListener("DOMContentLoaded", function () {
    const deleteButtons = document.querySelectorAll(".delete-search");

    deleteButtons.forEach((button) => {
        button.addEventListener("click", function (e) {
            e.preventDefault();

            const searchWord = button.getAttribute("data-search-word");
            const rowIndex = button.getAttribute("data-row-index");

            if (confirm("Are you sure you want to delete this search?")) {
                // Send an AJAX request to delete the search
                fetch(savedSearchesData.ajax_url, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: new URLSearchParams({
                        action: "delete_user_search",
                        search_word: searchWord,
                        _ajax_nonce: savedSearchesData.nonce,
                    }),
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            // Remove the row dynamically
                            const row = document.getElementById("search-row-" + rowIndex);
                            if (row) {
                                row.remove();
                            }
                        } else {
                            alert("Error deleting search: " + data.data);
                        }
                    })
                    .catch((error) => {
                        console.error("Error:", error);
                    });
            }
        });
    });
});