/**
 * Form Helper Utilities
 * Utilities for working with forms in Alpine components
 */

/**
 * Serialize form data into a plain object
 * @param {HTMLFormElement} form - The form element to serialize
 * @returns {Object} - Form data as key-value pairs
 */
export function serializeForm(form) {
    if (!(form instanceof HTMLFormElement)) {
        console.error('[serializeForm] Expected HTMLFormElement, got:', form);
        return {};
    }

    const formData = new FormData(form);
    const data = {};

    for (const [key, value] of formData.entries()) {
        // Handle checkboxes - if value is 'on', convert to '1', otherwise keep the value
        if (value === 'on') {
            data[key] = '1';
        } else {
            data[key] = value;
        }
    }

    return data;
}

/**
 * Serialize form data including unchecked checkboxes
 * @param {HTMLFormElement} form - The form element to serialize
 * @returns {Object} - Form data with explicit checkbox values
 */
export function serializeFormWithCheckboxes(form) {
    if (!(form instanceof HTMLFormElement)) {
        console.error('[serializeFormWithCheckboxes] Expected HTMLFormElement, got:', form);
        return {};
    }

    const data = serializeForm(form);

    // Find all checkboxes and ensure they have explicit values
    const checkboxes = form.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        if (!data.hasOwnProperty(checkbox.name)) {
            data[checkbox.name] = '0';
        } else if (checkbox.checked) {
            data[checkbox.name] = '1';
        }
    });

    return data;
}

/**
 * Read individual form field value safely
 * @param {HTMLFormElement} form - The form element
 * @param {string} fieldName - Name of the field to read
 * @param {*} defaultValue - Default value if field not found
 * @returns {*} - Field value or default
 */
export function getFieldValue(form, fieldName, defaultValue = '') {
    if (!(form instanceof HTMLFormElement)) {
        return defaultValue;
    }

    const field = form.querySelector(`[name="${fieldName}"]`);

    if (!field) {
        return defaultValue;
    }

    if (field.type === 'checkbox') {
        return field.checked ? '1' : '0';
    }

    return field.value || defaultValue;
}

/**
 * Clear all fields in a form
 * @param {HTMLFormElement} form - The form to clear
 */
export function clearForm(form) {
    if (!(form instanceof HTMLFormElement)) {
        console.error('[clearForm] Expected HTMLFormElement, got:', form);
        return;
    }

    form.reset();
}

/**
 * Set form field values from an object
 * @param {HTMLFormElement} form - The form element
 * @param {Object} data - Data to populate into form
 */
export function populateForm(form, data) {
    if (!(form instanceof HTMLFormElement)) {
        console.error('[populateForm] Expected HTMLFormElement, got:', form);
        return;
    }

    Object.keys(data).forEach(key => {
        const field = form.querySelector(`[name="${key}"]`);
        if (field) {
            if (field.type === 'checkbox') {
                field.checked = data[key] === '1' || data[key] === true;
            } else {
                field.value = data[key];
            }
        }
    });
}
