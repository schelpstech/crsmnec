document.querySelector('.registration-form').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const messageContainer = document.querySelector('.form-messages');
    const submitButton = form.querySelector('button[type="submit"]');
    let isValid = true;

    // Reset inline error messages
    form.querySelectorAll('.error-message').forEach(el => el.remove());

    function showInlineError(input, message) {
        const error = document.createElement('div');
        error.className = 'error-message';
        error.style.color = 'red';
        error.style.fontSize = '0.9em';
        error.textContent = message;
        input.parentNode.appendChild(error);
        isValid = false;
    }

    function showToast(message, success = false) {
        Toastify({
            text: message,
            duration: 4000,
            gravity: "top",
            position: "right",
            backgroundColor: success ? "#28a745" : "#dc3545",
            stopOnFocus: true
        }).showToast();
    }

    // Required field validations
    const requiredFields = [
        { name: 'surname', errorMessage: 'Surname is required.' },
        { name: 'firstname', errorMessage: 'First name is required.' },
        { name: 'gender', errorMessage: 'Gender is required.' },
        { name: 'phone', errorMessage: 'Phone number is required.' },
        { name: 'email', errorMessage: 'Email address is required.' },
        { name: 'education_profession', errorMessage: 'Please specify if you are in the Education Profession.' },
        { name: 'education_section', errorMessage: 'Please select your Education Sector.' },
        { name: 'certificate_name', errorMessage: 'Please enter your name for the certificate.' },
        { name: 'expectation', errorMessage: 'Please specify your expectations.' }
    ];

    requiredFields.forEach(field => {
        const input = form.querySelector(`[name="${field.name}"]`);
        if (!input || !input.value.trim()) {
            showInlineError(input, field.errorMessage);
        }
    });

    // Validate phone number
    const phoneInput = form.querySelector('[name="phone"]');
    const phone = phoneInput?.value.trim();
    if (phone && !/^\d{11}$/.test(phone)) {
        showInlineError(phoneInput, 'Phone number must be exactly 11 digits.');
    }

    // Validate email format
    const emailInput = form.querySelector('[name="email"]');
    const email = emailInput?.value.trim();
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showInlineError(emailInput, 'Please enter a valid email address.');
    }

    // Stop submission if invalid
    if (!isValid) {
        messageContainer.textContent = 'Please fix the errors below.';
        messageContainer.style.color = 'red';
        return;
    }

    // Submit via fetch
    submitButton.disabled = true;
    submitButton.textContent = 'Submitting...';

    fetch('./assets/php/formhandler.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            messageContainer.textContent = '';
            showToast(data.message || 'Registration successful!', true);
            form.reset();
        } else {
            showToast(data.message || 'Submission failed. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error. Please try again.');
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.textContent = 'Register Now';
    });
});
