@props(['chartData'])

@php
    $climber1ChartData = array_shift($chartData);
    $climber2ChartData = array_shift($chartData);

    $max = max(array_reduce(Arr::first($climber1ChartData), 'max'), array_reduce(Arr::first($climber1ChartData), 'max'));
    $maxScale = ceil($max / 10) * 10;
@endphp

<div class="mb-10">
    <div class="pb-1 mb-2 px-3">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">6a - 6c+ chart alles</h2>
    </div>
    <div class="grid grid-cols-2">

        <div x-data="{
    labels: ['6c+', '6c', '6b+', '6b', '6a+', '6a'],
    values: @json($climber1ChartData),
    init() {
        let chart = new Chart(this.$refs.canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: this.labels,
                datasets: [{
                    data: this.values.shift(),
                    backgroundColor: '#4F46E5',
                    borderColor: '#4F46E5',
                }, {
                    data: this.values.shift(),
                    backgroundColor: '#f59e0b',
                    borderColor: '#f59e0b',
                }
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                interaction: { intersect: false },
                scales: {
                    y: { beginAtZero: true, display: false },
                    x: {
                        reverse: true,
                        max: {{ $maxScale }},
                    }
                },
                plugins: {
                    legend: { display: false },
                }
            }
        })

        this.$watch('values', () => {
            chart.data.labels = this.labels
            chart.data.datasets[0].data = this.values
            chart.update()
        })
    }
}">
            <canvas x-ref="canvas" height="350"></canvas>
        </div>

        <div x-data="{
    labels: ['6c+', '6c', '6b+', '6b', '6a+', '6a'],
    values: @json($climber2ChartData),
    init() {
        let chart = new Chart(this.$refs.canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: this.labels,
                    datasets: [{
                        data: this.values.shift(),
                        backgroundColor: '#4F46E5',
                        borderColor: '#4F46E5',
                    }, {
                        data: this.values.shift(),
                        backgroundColor: '#f59e0b',
                        borderColor: '#f59e0b',
                    }
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                interaction: { intersect: false },
                scales: {
                    y: { beginAtZero: true, display: false },
                    x: { reverse: false, max: {{ $maxScale }} }
                },
                plugins: {
                    legend: { display: false },
                }
            }
        })

        this.$watch('values', () => {
            chart.data.labels = this.labels
            chart.data.datasets[0].data = this.values
            chart.update()
        })
    }
}">
            <canvas x-ref="canvas" height="350"></canvas>
        </div>
    </div>
</div>
