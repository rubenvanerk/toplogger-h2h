@props(['chartData', 'strengthHistory'])

@php
    $max = max(array_reduce(Arr::first($chartData['Ruben']), 'max'), array_reduce(Arr::first($chartData['Wouter']), 'max'));
    $maxScale = ceil($max / 10) * 10;
@endphp

<div {{ $attributes->class(['mb-10']) }}>
    <div class="pb-1 mb-2 px-3">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">6a - 6c+ chart alles</h2>
    </div>
    <div class="grid grid-cols-11">
        @foreach($chartData as $name => $data)
            <div class="col-span-5" x-data="{
                    labels: ['6c+', '6c', '6b+', '6b', '6a+', '6a'],
                    values: @json($data),
                    init() {
                        let chart = new Chart(this.$refs.canvas.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: this.labels,
                                    datasets: [{
                                        data: this.values.shift(),
                                        backgroundColor: '#4F46E5',
                                        borderColor: '#4F46E5',
                                        label: 'Tops',
                                    }, {
                                        data: this.values.shift(),
                                        backgroundColor: '#f59e0b',
                                        borderColor: '#f59e0b',
                                        label: 'Flashes',
                                    }
                                ],
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                interaction: { intersect: false },
                                scales: {
                                    y: { display: false },
                                    x: {
                                        reverse: {{ $loop->first ? 'true' : 'false' }},
                                        max: {{ $maxScale }},
                                    }
                                },
                                plugins: {
                                    legend: { display: false },
                                }
                            }
                        })
                    }
                }">
                <div class="mb-1">
                    <span
                        class="text-2xl font-bold leading-7 text-gray-800 sm:text-3xl sm:truncate pl-3">{{ $name }}</span>
                </div>
                <canvas x-ref="canvas" height="350"></canvas>
            </div>

            @if ($loop->first)
                <div class="flex flex-col text-xs text-gray-600 justify-between items-center pb-9 pt-9">
                    <span>6c+</span>
                    <span>6c</span>
                    <span>6b+</span>
                    <span>6b</span>
                    <span>6a+</span>
                    <span>6a</span>
                </div>
            @endif
        @endforeach
    </div>
</div>

<div {{ $attributes->class(['mb-10']) }}>
    <div class="pb-1 mb-2 px-3">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">Kracht historie</h2>
    </div>


    <div x-data="{
                labels: {{ json_encode($strengthHistory['labels']) }},
                values: {{ json_encode($strengthHistory['data']) }},
                init() {
                    let chart = new Chart(this.$refs.canvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: this.labels,
                            datasets: [
                                {
                                    data: this.values.shift(),
                                    backgroundColor: '#4F46E5',
                                    borderColor: '#4F46E5',
                                    label: 'Ruben'
                                }, {
                                    data: this.values.shift(),
                                    backgroundColor: '#f59e0b',
                                    borderColor: '#f59e0b',
                                    label: 'Wouter'
                                }
                            ],
                        },
                    })
                }
            }">
        <canvas x-ref="canvas" height="350"></canvas>
    </div>
</div>
