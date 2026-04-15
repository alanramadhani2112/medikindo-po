{{-- BAR CHART COMPONENT --}}
{{-- Purpose: Display comparison data in bars --}}
{{-- Usage: <x-charts.bar-chart :chart-id="'monthlyRevenue'" :title="'Monthly Revenue'" :labels="$labels" :datasets="$datasets" /> --}}

@props([
    'chartId' => 'barChart',
    'title' => 'Bar Chart',
    'labels' => [],
    'datasets' => [],
    'height' => '300',
    'showLegend' => true,
    'horizontal' => false,
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

    new Chart(ctx, {
        type: '{{ $horizontal ? "bar" : "bar" }}',
        data: {
            labels: @json($labels),
            datasets: @json($datasets)
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: '{{ $horizontal ? "y" : "x" }}',
            plugins: {
                legend: {
                    display: {{ $showLegend ? 'true' : 'false' }},
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12,
                            family: 'Inter'
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
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.{{ $horizontal ? 'x' : 'y' }} !== null) {
                                label += new Intl.NumberFormat('id-ID').format(context.parsed.{{ $horizontal ? 'x' : 'y' }});
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                {{ $horizontal ? 'x' : 'y' }}: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        font: {
                            size: 11,
                            family: 'Inter'
                        },
                        callback: function(value) {
                            if (value >= 1000000000) {
                                return (value / 1000000000).toFixed(1) + 'B';
                            } else if (value >= 1000000) {
                                return (value / 1000000).toFixed(1) + 'M';
                            } else if (value >= 1000) {
                                return (value / 1000).toFixed(1) + 'K';
                            }
                            return value;
                        }
                    }
                },
                {{ $horizontal ? 'y' : 'x' }}: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11,
                            family: 'Inter'
                        }
                    }
                }
            }
        }
    });
});
</script>
