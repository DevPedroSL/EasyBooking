window.toggleDropdown = function toggleDropdown(dropdownId = 'user-dropdown') {
    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) {
        return;
    }

    dropdown.classList.toggle('hidden');
};

document.addEventListener('click', (event) => {
    [
        ['user-menu-button', 'user-dropdown'],
    ].forEach(([buttonId, dropdownId]) => {
        const dropdown = document.getElementById(dropdownId);
        const button = document.getElementById(buttonId);

        if (button && dropdown && !button.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });
});
