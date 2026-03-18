function formatPhone(value) {
    const digits = value.replace(/\D/g, '').slice(0, 10); // keep max 10 digits
    const parts = [];
    if (digits.length > 0) parts.push(digits.slice(0, 3));
    if (digits.length > 3) parts.push(digits.slice(3, 6));
    if (digits.length > 6) parts.push(digits.slice(6, 10));
    return parts.join('-');
}

function attachPhoneMask(input) {
    input.addEventListener('input', () => {
        const start = input.selectionStart;
        const prev = input.value;
        input.value = formatPhone(prev);

        // naive caret fix: move to end if formatting changed
        if (input.value !== prev) {
            input.setSelectionRange(input.value.length, input.value.length);
        }
    });
}

    // Usage: call after DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const phone = document.querySelector('input[name="phone"]');
    if (phone) attachPhoneMask(phone);
});