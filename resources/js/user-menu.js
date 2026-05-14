window.toggleDropdown = function toggleDropdown(dropdownId = 'user-dropdown') {
    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) {
        return;
    }

    dropdown.classList.toggle('hidden');

    const button = document.querySelector(`[aria-controls="${dropdownId}"]`) || document.getElementById('user-menu-button');
    if (button) {
        button.setAttribute('aria-expanded', String(!dropdown.classList.contains('hidden')));
    }
};

document.addEventListener('click', (event) => {
    [
        ['user-menu-button', 'user-dropdown'],
    ].forEach(([buttonId, dropdownId]) => {
        const dropdown = document.getElementById(dropdownId);
        const button = document.getElementById(buttonId);

        if (button && dropdown && !button.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false');
        }
    });
});
