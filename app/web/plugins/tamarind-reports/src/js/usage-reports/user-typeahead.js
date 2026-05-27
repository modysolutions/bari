import apiFetch from "@wordpress/api-fetch";

const UserTypeahead = {
    init: () => {
        const userSearchInput = document.getElementById('tm-reports-user-search');
        const userListDatalist = document.getElementById('tm-reports-user-list');
        const hiddenUserIdInput = document.getElementById('tm-reports-user-id');
        let lastSelectedEmail = '';

        if(userSearchInput) {
            userSearchInput.addEventListener('input', (event) => {
                const searchTerm = event.target.value.toLowerCase();

                // Only clear user_id if the email changed from last selection
                if (searchTerm !== lastSelectedEmail.toLowerCase()) {
                    hiddenUserIdInput.value = '';
                }

                if (searchTerm.length < 2) {
                    userListDatalist.innerHTML = '';
                    return;
                }

                // Try to use preloaded users first (faster, more reliable)
                const preloadedUsers = window.tamarind_reports_vars?.users;
                
                if (preloadedUsers && preloadedUsers.length > 0) {
                    // Fast path: filter preloaded data
                    const filtered = preloadedUsers.filter(user => 
                        user.email.includes(searchTerm) || 
                        user.name.toLowerCase().includes(searchTerm)
                    ).slice(0, 25);

                    userListDatalist.innerHTML = '';
                    filtered.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.email;
                        option.setAttribute('data-user-id', user.user_id);
                        option.setAttribute('data-user-name', user.name);
                        userListDatalist.appendChild(option);
                    });
                } else {
                    // Fallback: use API with edit context to get email field
                    apiFetch({
                        path: `/wp/v2/users?search=${searchTerm}&per_page=25&context=edit`,
                        method: 'GET',
                        credentials: 'include'
                    })
                        .then(users => {
                            userListDatalist.innerHTML = '';
                            users.forEach(user => {
                                const option = document.createElement('option');
                                option.value = user.email || user.name;
                                option.setAttribute('data-user-id', user.id);
                                option.setAttribute('data-user-name', user.name);
                                userListDatalist.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error searching for users: ', error);
                        });
                }
            });

            // Use capture phase to execute before triggers.js filter handler
            userSearchInput.addEventListener('change', (event) => {
                const selectedOption = userListDatalist.querySelector(`option[value="${event.target.value}"]`);

                if (selectedOption) {
                    hiddenUserIdInput.value = selectedOption.getAttribute('data-user-id');
                    lastSelectedEmail = event.target.value;
                } else {
                    hiddenUserIdInput.value = '';
                    lastSelectedEmail = '';
                }
            }, true); // Capture phase - runs first
        }
    }
}

export default UserTypeahead;