document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('[data-dropdown]');

    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        const menu = dropdown.querySelector('.dropdown-menu');
        const arrow = dropdown.querySelector('.dropdown-arrow');

        // Toggle dropdown on click
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const isOpen = dropdown.classList.contains('dropdown-open');

            // Close all other dropdowns
            dropdowns.forEach(other => {
                if (other !== dropdown) {
                    other.classList.remove('dropdown-open');
                    other.querySelector('.dropdown-trigger').setAttribute('aria-expanded', 'false');
                }
            });

            // Toggle current dropdown
            if (isOpen) {
                dropdown.classList.remove('dropdown-open');
                trigger.setAttribute('aria-expanded', 'false');
            } else {
                dropdown.classList.add('dropdown-open');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });

        // Close dropdown when clicking menu items
        menu.addEventListener('click', () => {
            dropdown.classList.remove('dropdown-open');
            trigger.setAttribute('aria-expanded', 'false');
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', () => {
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('dropdown-open');
            dropdown.querySelector('.dropdown-trigger').setAttribute('aria-expanded', 'false');
        });
    });

    // Handle mobile menu toggle
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');

    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', () => {
            const isOpen = mobileToggle.getAttribute('aria-expanded') === 'true';
            mobileToggle.setAttribute('aria-expanded', !isOpen);
            mobileMenu.classList.toggle('mobile-menu-open');
        });
    }
});