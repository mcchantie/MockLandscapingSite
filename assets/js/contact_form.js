function display_message(fieldName, message, success) {
    let element;
    let submitButton = document.getElementById("submit-message");
    if(success){
        submitButton.classList.remove('error-msg');
        submitButton.classList.add('success-msg');
        submitButton.innerHTML= 'Thank you for contacting us! We will get back to you shortly.';
    }
    else {
        element = document.getElementById(fieldName+"-error");
        if (element) {
            if(fieldName === 'attachments'){
                element.innerHTML = message;
            }
            element.classList.remove('opacity-0');
            element.classList.add('opacity-100');
        }
        submitButton.classList.remove('success-msg');
        submitButton.classList.add('error-msg');
        submitButton.innerHTML= 'Please check for errors and try again.';
    }
    submitButton.classList.remove('opacity-0');
    submitButton.classList.add('opacity-100');
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action="/api/send_email.php"]');
    if (!form) {
        console.error('Form not found!');
        return;
    }

    console.log('Form found, attaching submit handler');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Form submitted via JavaScript');
        // Track form submission attempt
        if (typeof gtag !== 'undefined') {
            gtag('event', 'form_submit_attempt', {
                event_category: 'Contact Form',
                event_label: 'Contact Form Submission'
            });
        }

        // Optional: disable submit
        const submit = form.querySelector('input[type="submit"], button[type="submit"]');
        submit?.setAttribute('disabled', 'true');

        // Clear previous errors
        form.querySelectorAll('.error-msg').forEach(n => {n.classList.remove('opacity-100'); n.classList.add('opacity-0');});
        // Clear previous success message
        form.querySelectorAll('.success-msg').forEach(n => {n.classList.remove('opacity-100'); n.classList.add('opacity-0');});

        // Check total attachment size to prevent from exceeding POST Content-Length limit
        const attachments = form.querySelector('#attachments');
        if (attachments && attachments.files.length > 0) {
            const maxFileSize = 10 * 1024 * 1024; // 10MB per file
            const maxTotalSize = 20 * 1024 * 1024; // 20MB total
            let totalSize = 0;

            for (let file of attachments.files) {
                if (file.size > maxFileSize) {
                    // Show error for oversized individual file
                    // alert(`"${file.name}" exceeds 10MB limit. Please choose a smaller file.`);
                    // Or integrate with your error system:
                     display_message('attachments', file.name + ' exceeds 10MB limit. Please choose a smaller file.',false);

                    submit?.removeAttribute('disabled'); // Re-enable submit
                    return; // Stop submission
                }
                totalSize += file.size; // Accumulate total
            }

            if (totalSize > maxTotalSize) {
                // Show error (customize as needed, e.g., using your display_message)
                // alert('Total attachments exceed 20MB. Please remove some files or use smaller ones.');
                // Or integrate with your error system:
                 display_message('attachments', 'Total attachments exceed 20MB. Please remove some files or use smaller ones.', false);

                submit?.removeAttribute('disabled'); // Re-enable submit
                return; // Stop submission
            }
        }

        const url = form.action;
        const formData = new FormData(form);

        try {
            const resp = await fetch(url, { method: 'POST', body: formData });
            const isJSON = resp.headers.get('content-type')?.includes('application/json');
            const data = isJSON ? await resp.json() : { ok: resp.ok };

            console.log('Response:', data);

            if (!data.ok) {
                const errors = data.errors || {};
                Object.entries(errors).forEach(([index, input]) => {
                    if(index === 'attachments'){
                        display_message(index, input, false);
                    }else {
                        display_message(input, '', false);
                    }
                });
                // Track form errors
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'form_submit_error', {
                        event_category: 'Contact Form',
                        event_label: 'Contact Form Error',
                        value: result.message || 'Unknown error'
                    });
                }
                return;
            }

            form.reset();
            // Track successful submission
            if (typeof gtag !== 'undefined') {
                gtag('event', 'form_submit_success', {
                    event_category: 'Contact Form',
                    event_label: 'Contact Form Success',
                    value: 1
                });

                // Track as conversion if this is a lead/contact goal
                // gtag('event', 'conversion', {
                //     send_to: 'AW-CONVERSION_ID/CONVERSION_LABEL' // Replace with your conversion tracking ID if you have Google Ads
                // });
            }
            display_message('submit', '',true);
        } catch (err) {
            console.error('Error:', err);
            alert('Submission failed. Please try again later.');
            // Track technical errors
            if (typeof gtag !== 'undefined') {
                gtag('event', 'form_submit_error', {
                    event_category: 'Contact Form',
                    event_label: 'Technical Error',
                    value: error.message
                });
            }
        } finally {
            submit?.removeAttribute('disabled');
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Handle the quote checkbox specifically
    const quoteCheckboxContainer = document.querySelector('#collapsable-menu .group');
    const quoteCheckbox = document.getElementById('quote');
    /* This to prevent the checkbox from becoming unchecked
       at the end of the quoteBox callback function on mobile.
       This issue makes it appear that the box is never checked. */
    //quoteCheckbox.preventDefault();
    const quoteBox = document.getElementById('quote-box');

    if (quoteCheckboxContainer && quoteCheckbox && quoteBox) {
        quoteBox.addEventListener('click', function(e) {
            // Prevent the default behavior to avoid conflicts
            e.preventDefault();
            e.stopPropagation();

            if (quoteBox.contains(e.target) && e.target !== quoteCheckbox) {
                console.log('Quote box clicked');
                // Toggle the checkbox state
                quoteCheckbox.checked = !quoteCheckbox.checked;
                // Trigger change event for any CSS transitions
                quoteCheckbox.dispatchEvent(new Event('change'));
            }
        });

        // Handle direct clicks on the checkbox
        quoteCheckbox.addEventListener('click', function(e) {
            // Allow the default checkbox behavior
            e.stopPropagation();
            console.log('Quote checkbox directly clicked, checked:', this.checked);
        });
    }

    // Handle all service checkboxes in the collapsible menu
    const servicesContainer = document.getElementById('services-checklist');
    if (servicesContainer) {
        const serviceItems = servicesContainer.querySelectorAll('div');

        serviceItems.forEach(function(serviceDiv) {
            const checkbox = serviceDiv.querySelector('input[type="checkbox"]');
            const label = serviceDiv.querySelector('label');

            if (checkbox && label) {
                // Add click handler to the entire service div
                serviceDiv.addEventListener('click', function(e) {
                    // Toggle checkbox when clicking anywhere in the service item
                    // checkbox.checked = !checkbox.checked;
                    // checkbox.dispatchEvent(new Event('change'));
                    console.log('Service clicked:', checkbox.value, 'is now:', checkbox.checked);
                });

                // Prevent double-toggling when clicking directly on the checkbox
                checkbox.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });
    }
});