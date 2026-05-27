document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.tm-options-level--one [type=checkbox]').forEach(element => {
        const level1_container = element.closest('.tm-options-level--one');
        element.addEventListener('click', function () {
            const bothCheckbox = level1_container.querySelector('[value="both"]');
            const otherCheckboxes = Array.from(level1_container.querySelectorAll('[type=checkbox]')).filter(cb => cb.value !== 'both');

            if (element.checked) {
                if (element.value === 'both') {
                    otherCheckboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                } else {
                    if (bothCheckbox && bothCheckbox.checked) {
                        bothCheckbox.checked = false;
                    }
                }
            } else {
                if (element.value !== 'both') {
                    if (bothCheckbox && bothCheckbox.checked) {
                        bothCheckbox.checked = false;
                    }
                }
            }

            const allOthersChecked = otherCheckboxes.every(cb => cb.checked);
            if (allOthersChecked && bothCheckbox) {
                bothCheckbox.checked = true;
            } else if (bothCheckbox && bothCheckbox.checked && !element.checked && element.value !== 'both') {
                bothCheckbox.checked = false;
            }
        });
    });

    document.querySelectorAll('.tm-newsletter-subscribe-button').forEach(button => {
        const text = button.innerHTML;
        button.addEventListener('click', function () {
            button.disabled = true;
            button.innerHTML = 'Saving...';
            const groupName = button.dataset.groupName;
            const group = button.closest('.tm-post-card');
            if (!group) return;

            const selectedLevel1Elements = group.querySelectorAll('.newsletter-level1') || [];
			const selectedLevel2 = group.querySelector('.newsletter-level2:checked')?.value || '';

            const dataOptions = group.querySelector('.tm-post-card__options').querySelector('.tm-layout-grid').dataset.options;

            let optionMap = [];
            optionMap = JSON.parse(dataOptions || '[]');
            
            if (optionMap.length === 0) {
                return;
            }

            let userSettings = {
                level1: [],
                level2: '',
            };

            let match = null;

            const userGroups = {};
            userGroups[groupName] = [];

            const bothCheckbox = Array.from(selectedLevel1Elements).find(el => el.value === 'both' && el.checked);

            if (bothCheckbox) {
                match = optionMap.find(opt => {
                    return opt?.level1_slug === 'both' && opt?.level2_slug === selectedLevel2;
                });
                if (match) {
                    userGroups[groupName].push(match.list_id);
                    userSettings.level1.push('both');
                    userSettings.level2 = selectedLevel2;
                }
            } else {
                if(selectedLevel1Elements.length === 0 && selectedLevel2 !== '') {
                    match = optionMap.find(opt => {
                        return opt?.level2_slug === selectedLevel2
                    });
                    userGroups[groupName].push(match?.list_id);
                    userSettings.level2 = selectedLevel2;
                }

                selectedLevel1Elements.forEach((selectedLevel1Element) => {
                    const selectedLevel1 = selectedLevel1Element.value;
                    match = optionMap.find(opt => {
                        return opt?.level1_slug === selectedLevel1 && opt?.level2_slug === selectedLevel2;
                    });
                    if(selectedLevel1Element.checked) {
                        userGroups[groupName].push(match?.list_id);
                        userSettings.level1.push(selectedLevel1);
                    }
                    userSettings.level2 = selectedLevel2;
                });
            }
            submitForm(group, groupName, optionMap, userGroups, userSettings);
        });

        const submitForm = (group, groupName, optionMap, userGroups, userSettings) => {
            const payload = {
                action: 'tm_newsletter_subscribe',
                nonce: tmUserArea?.nonce,
                groups: JSON.stringify(userGroups),
                userSettings: JSON.stringify(userSettings),
            }

            const message = group.querySelector('.tm-newsletter-subscribe-message');
            if (message) {
                message.style.display = 'none';
                message.innerHTML = '';
            }

            fetch(tmUserArea.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(payload)
            })
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        if (message) {
                            message.innerHTML = response?.data?.message ?? 'Your preferences have been saved!';
                            message.style.display = 'flex';
                        }
                        button.disabled = false;
                        button.innerHTML = text;
                    }
                })
                .catch(() => {
                    console.log('Something went wrong. Please try again.');
                });
        }
    });
});
