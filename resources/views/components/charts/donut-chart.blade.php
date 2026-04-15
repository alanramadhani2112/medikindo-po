{{-- DONUT CHART COMPONENT --}}
{{-- Purpose: Display distribution/percentage data --}}
{{-- Usage: <x-charts.donut-chart :chart-id="'poStatus'" :title="'PO by Status'" :labels="$labels" :data="$data" :colors="$colors" /> --}}

@props([
    'chartId' => 'donutChart',
    'title' => 'Donut Chart',
    'labels' => [],
    'data' => [],
    'colors' => [],
    'height' => '300',
    'showLegend' => true,
])

<div class="card card-flush h-100">
    <div class="card-header border-0 pt-6">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-gray-900 fs-3">{{ $title }}</span>
            @if(isset($subtitle))
            <span class="text-muted mt-1 fw-semibold fs-7">{{ $subtitle }}</span>
            @endif
        </h3>
        @if(isset($actions))
        <div class="card-toolbar">
            {{ $actions }}
        </div>
        @endif
    </div>
    <div class="card-body pt-3">
        <div style="position: relative; height: {{ $height }}px;">
            <canvas id="{{ $chartId }}"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('{{ $chartId }}');
    if (!ctx) return;

    const chartData = @json($data);
    const chartLabels = @json($labels);
    const chartColors = @json($colors);

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartLabels,
            datasets: [{
                data: chartData,
                backgroundColor: chartColors.length > 0 ? chartColors : [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                ],
                borderColor: '#ffffff',
                borderWidth: 2,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: {{ $showLegend ? 'true' : 'false' }},
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12,
                            family: 'Inter'
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return {
                                        text: `${label}: ${percentage}%`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 13,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 12
                    },
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${new Intl.NumberFormat('id-ID').format(value)} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%', // Makes it a donut (vs pie)
        }
    });
});
</script>
