document.addEventListener('DOMContentLoaded', function() {
    // Check if gtag is available
    if (typeof gtag === 'undefined') {
        console.warn('Google Analytics gtag not found');
        return;
    }

    // Track form interactions
    function trackFormEvent(eventName, formName, additionalParams = {}) {
        gtag('event', eventName, {
            event_category: 'Form',
            event_label: formName,
            ...additionalParams
        });

        console.log(`GA Event: ${eventName} - ${formName}`, additionalParams);
    }

    // Find all forms on the page
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        const formName = form.id || form.name || form.className || 'unnamed-form';
        let hasStarted = false;
        let fieldsInteracted = new Set();

        // Track form start (first interaction)
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                if (!hasStarted) {
                    hasStarted = true;
                    trackFormEvent('form_start', formName, {
                        form_id: formName,
                        page_location: window.location.href
                    });
                }

                // Track field interactions
                const fieldName = this.name || this.id || this.type;
                if (!fieldsInteracted.has(fieldName)) {
                    fieldsInteracted.add(fieldName);
                    trackFormEvent('form_field_interaction', formName, {
                        field_name: fieldName,
                        field_type: this.type,
                        form_id: formName
                    });
                }
            });
        });

        // Track form submission attempts
        form.addEventListener('submit', function(e) {
            trackFormEvent('form_submit', formName, {
                form_id: formName,
                fields_completed: fieldsInteracted.size,
                page_location: window.location.href
            });
        });

        // Track form abandonment (user leaves page without submitting)
        let submitted = false;
        form.addEventListener('submit', () => submitted = true);

        window.addEventListener('beforeunload', function() {
            if (hasStarted && !submitted) {
                trackFormEvent('form_abandon', formName, {
                    form_id: formName,
                    fields_completed: fieldsInteracted.size,
                    page_location: window.location.href
                });
            }
        });
    });

    // Track successful form submissions (if using AJAX)
    window.trackFormSuccess = function(formName, additionalData = {}) {
        trackFormEvent('form_success', formName, {
            form_id: formName,
            page_location: window.location.href,
            ...additionalData
        });
    };

    // Track form errors
    window.trackFormError = function(formName, errorType, errorMessage = '') {
        trackFormEvent('form_error', formName, {
            form_id: formName,
            error_type: errorType,
            error_message: errorMessage,
            page_location: window.location.href
        });
    };
});