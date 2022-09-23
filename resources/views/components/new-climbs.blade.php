@props(['gyms'])

@php
    $colors = [
        '#ef4444', // red-500
        '#F97316', // orange-500
        '#10B981', // emerald-500
        '#06B6D4', // cyan-500
        '#6366F1', // indigo-500
        '#D946EF', // fucksia-500
        '#f97316', // orange-500
        '#84cc16', // lime-500
    ];
@endphp

<div {{ $attributes->class(['px-3 mb-10']) }}>
    <div class="border-b border-gray-200 pb-1 mb-2 flex justify-between items-center">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">Nieuwe boulders</h2>
    </div>

    <div class="my-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle">
                <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5">
                    <table class="w-full divide-y divide-gray-300 table-fixed">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Gym</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Laatste sessie</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Nieuw</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($gyms as $gym)
                        <tr x-data>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900"><div class="truncate">{{ $gym->name }}</div></td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-tooltip="{{ $gym->last_session_at->locale('nl')->isoFormat('LL') }}">
                                {{ $gym->last_session_at->diffForHumans() }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $gym->new_climbs->count() }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div x-data="{
                    init() {
                        let chart = new Chart(this.$refs.canvas.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: ['6a', '6a+', '6b', '6b+', '6c', '6c+'],
                                    datasets: [
                                    @foreach($gyms as $gym)
                                    {
                                        data: @json($gym->chart_data),
                                        backgroundColor: '{{ $colors[$loop->index] }}',
                                        borderColor: '{{ $colors[$loop->index] }}',
                                        label: '{{ $gym->name }}',
                                    }, @endforeach
                                ],
                            },
                            options: {
                                indexAxis: 'x',
                                responsive: true,
                                plugins: {
                                    legend: { display: true },
                                }
                            }
                        })
                    }
                }">
        <canvas x-ref="canvas" height="200"></canvas>
    </div>
</div>
