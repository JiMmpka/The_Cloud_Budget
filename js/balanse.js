document.addEventListener('DOMContentLoaded', function() {
    const ctxPieChart = document.getElementById('expensesChart');
    if (ctxPieChart) {
        const ctx = ctxPieChart.getContext('2d');
        const expensesChartDynamic = new Chart(ctx, {
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

        // Function to resize the chart
        function resizeChart() {
            expensesChartDynamic.resize();
        }
        // Listening for window resize events
        window.addEventListener('resize', resizeChart);
        // Initial call to the resizeChart function
        resizeChart();
    }

    // Handling the modal with dates
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const periodForm = document.getElementById('periodForm');

    if (startDateInput && endDateInput) {
        // Setting the date format to yyyy-mm-dd
        startDateInput.setAttribute('min', '2000-01-01');
        endDateInput.setAttribute('min', '2000-01-01');
        // Setting today's date as the maximum
        const today = new Date().toISOString().split('T')[0];
        startDateInput.setAttribute('max', today);
        endDateInput.setAttribute('max', today);

        // Date validation
        startDateInput.addEventListener('change', function() {
            endDateInput.setAttribute('min', this.value);
        });
        endDateInput.addEventListener('change', function() {
            startDateInput.setAttribute('max', this.value);
        });
    }

    // Form validation before submission
    if (periodForm) {
        periodForm.addEventListener('submit', function(event) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            if (startDate > endDate) {
                event.preventDefault();
                alert('End date cannot be earlier than start date');
            }
        });
    }
});