function loadHTML(elementId, url) {
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            document.getElementById(elementId).innerHTML = data;
        })
        .catch(error => alert('Error loading component: ' + error.message));
}

function showSuccessModal(message) {
    if (message) {
        $(document).ready(function() {
            $('#successModal').modal('show');
        });
    }
}

function getTodayDate() {
    return new Date().toISOString().split('T')[0];
}

function validateDate(dateString) {
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(dateString)) return false;
    const date = new Date(dateString);
    const minDate = new Date('2000-01-01');
    const maxDate = new Date();
    return !isNaN(date.getTime()) && date >= minDate && date <= maxDate;
}

document.addEventListener('DOMContentLoaded', function() {
    loadHTML('header', 'components_header.php');
    loadHTML('navbar', 'components_navbar.php');
    
    const $form = document.getElementById('form');
    const $dateInput = document.getElementById('date');
    const $modalDatepicker = $('#modalDatepicker');
    const $openDateModal = $('#openDateModal');
    const dateModalElement = document.getElementById('dateModal');

    // Only initialize modal-related functionality if the dateModal exists
    if (dateModalElement) {
        const dateModal = new bootstrap.Modal(dateModalElement);

        if ($modalDatepicker.length) {
            $modalDatepicker.datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
        }

        if ($openDateModal.length) {
            $openDateModal.on('click', () => dateModal.show());
        }

        // Modal datepicker functionality
        if ($modalDatepicker.length) {
            $modalDatepicker.on('changeDate', function(e) {
                const selectedDate = e.format(0, "yyyy-mm-dd");
                if (validateDate(selectedDate)) {
                    $dateInput.value = selectedDate;
                    dateModal.hide();
                } else {
                    alert("Invalid date. Please enter a date between 2000-01-01 and today.");
                }
            });
        }
    }

    // Initialize the input field with today's date if it's empty and exists
    if ($dateInput && !$dateInput.value) {
        $dateInput.value = getTodayDate();
    }

    // Form submission validation
    if ($form) {
        $form.addEventListener('submit', function(e) {
            if ($dateInput && !validateDate($dateInput.value)) {
                alert("The entered date is invalid or out of the allowed range.");
                e.preventDefault();
            }
        });
    }

    // Real-time validation on input change
    if ($dateInput) {
        $dateInput.addEventListener('input', function() {
            this.setCustomValidity(validateDate(this.value) ? '' : 'Invalid date. Please enter a date between 2000-01-01 and today.');
        });
    }

    // Only show success modal if it exists
    const successModal = document.getElementById('successModal');
    if (successModal && typeof successMessage !== 'undefined' && successMessage) {
        new bootstrap.Modal(successModal).show();
    }
});