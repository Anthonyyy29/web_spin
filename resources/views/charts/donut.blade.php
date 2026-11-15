<!-- chart.php -->
<script>
    const chartData_{{ $id }} = @json($source['source']);

    const config_{{ $id }} = {
        type: 'doughnut',
        data: {
            labels: chartData_{{ $id }}.labels,
            datasets: [{
                label: @json($source['label']),
                data: chartData_{{ $id }}.data,
                backgroundColor: chartData_{{ $id }}.backgroundColors,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    };

    document.addEventListener("DOMContentLoaded", function() {
        const myChart = new Chart(
            document.getElementById('{{ $id }}'),
            config_{{ $id }}
        );
    });
</script>