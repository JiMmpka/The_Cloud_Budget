document.addEventListener('DOMContentLoaded', function() {
    const ctxPieChart = document.getElementById('expensesChart').getContext('2d');
    
    const expensesChartDynamic = new Chart(ctxPieChart, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: 'Expenses Distribution',
                data: chartData,
                backgroundColor: backgroundColors,
                borderColor: backgroundColors.map(color => color.replace('0.7', '1')),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
            }
        }
    });

    // Funkcja do zmiany rozmiaru wykresu
    function resizeChart() {
        expensesChartDynamic.resize();
    }

    // Nasłuchiwanie na zmianę rozmiaru okna
    window.addEventListener('resize', resizeChart);

    // Inicjalne wywołanie funkcji resizeChart
    resizeChart();
	
	    // Obsługa modalu z datami
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const periodForm = document.getElementById('periodForm');

    // Ustawienie formatu daty na yyyy-mm-dd
    startDateInput.setAttribute('min', '2000-01-01');
    endDateInput.setAttribute('min', '2000-01-01');

    // Ustawienie dzisiejszej daty jako maksymalnej
    const today = new Date().toISOString().split('T')[0];
    startDateInput.setAttribute('max', today);
    endDateInput.setAttribute('max', today);

    // Walidacja dat
    startDateInput.addEventListener('change', function() {
        endDateInput.setAttribute('min', this.value);
    });

    endDateInput.addEventListener('change', function() {
        startDateInput.setAttribute('max', this.value);
    });

    // Walidacja formularza przed wysłaniem
    periodForm.addEventListener('submit', function(event) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (startDate > endDate) {
            event.preventDefault();
            alert('End date cannot be earlier than start date');
        }
    });
});