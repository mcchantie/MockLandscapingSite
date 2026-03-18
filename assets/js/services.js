document.addEventListener('DOMContentLoaded', () => {
    const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    if (isTouchDevice) {
        const cards = document.querySelectorAll('.group');

        cards.forEach(card => {
            card.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                // Simply toggle the is-hovered class
                card.classList.toggle('is-hovered');

                // Remove is-hovered from other cards (accordion behavior)
                cards.forEach(otherCard => {
                    if (otherCard !== card) {
                        otherCard.classList.remove('is-hovered');
                    }
                });
            });
        });
    }
})