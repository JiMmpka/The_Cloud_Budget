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

document.addEventListener('DOMContentLoaded', function() {
    loadHTML('header', 'components_header.html');
    loadHTML('navbar', 'components_navbar.html');

    const $dateInput = document.getElementById('date');
    const $modalDatepicker = $('#modalDatepicker');
    const $openDateModal = $('#openDateModal');
    const dateModal = new bootstrap.Modal(document.getElementById('dateModal'));

    $modalDatepicker.datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });

    $openDateModal.on('click', () => dateModal.show());

    $modalDatepicker.on('changeDate', function(e) {
        const selectedDate = e.format(0, "yyyy-mm-dd");
        if (validateDate(selectedDate)) {
            $dateInput.value = selectedDate;
            dateModal.hide();
        } else {
            alert("Invalid date. Please enter a date between 2000-01-01 and today.");
        }
    });

    // Initialize the input field with today's date if it's empty
    if (!$dateInput.value) {
        $dateInput.value = new Date().toISOString().split('T')[0];
    }

    // Form submission validation
    $('form').on('submit', function(e) {
        if (!validateDate($dateInput.value)) {
            alert("The entered date is invalid or out of the allowed range.");
            e.preventDefault();
        }
    });

    // Real-time validation on input change
    $dateInput.addEventListener('input', function() {
        this.setCustomValidity(validateDate(this.value) ? '' : 'Invalid date. Please enter a date between 2000-01-01 and today.');
    });

    function validateDate(dateString) {
        const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
        if (!dateRegex.test(dateString)) return false;

        const date = new Date(dateString);
        const minDate = new Date('2000-01-01');
        const maxDate = new Date();

        return !isNaN(date.getTime()) && date >= minDate && date <= maxDate;
    }
});