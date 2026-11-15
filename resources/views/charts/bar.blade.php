<script>
    const chartData_{{ $id }} = @json($source['source']);

    const config_{{ $id }} = {
        type: 'bar',
        data: {
            labels: chartData_{{ $id }}.labels,
            datasets: [{
                label: @json($source['label']),
                data: chartData_{{ $id }}.data,
                borderWidth: 1,
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            if (Number.isInteger(value)) {
                                return value;
                            }
                        }
                    }
                }
            }

        }
    };

    document.addEventListener("DOMContentLoaded", function() {
        const myChart = new Chart(
            document.getElementById('{{ $id }}'),
            config_{{ $id }}
        );
    });
</script>