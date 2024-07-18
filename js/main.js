function loadHTML(elementId, url) {
    fetch(url)
        .then(response => response.text())
        .then(data => {
            document.getElementById(elementId).innerHTML = data;
        })
        .catch(error => console.log('Error:', error));
}

document.addEventListener('DOMContentLoaded', function() {
    loadHTML('header', 'components_header.html');
    loadHTML('navbar', 'components_navbar.html');

    var $dateInput = $('#date');
    var $modalDatepicker = $('#modalDatepicker');
    var $openDateModal = $('#openDateModal');
    var dateModal = new bootstrap.Modal(document.getElementById('dateModal'));

    $dateInput.datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });

    $modalDatepicker.datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });

    $openDateModal.on('click', function() {
        dateModal.show();
    });

    $modalDatepicker.on('changeDate', function(e) {
        $dateInput.datepicker('setDate', e.date);
        dateModal.hide();
    });

    // Inicjalizacja z dzisiejszą datą
    var today = new Date();
    $dateInput.datepicker('setDate', today);
});