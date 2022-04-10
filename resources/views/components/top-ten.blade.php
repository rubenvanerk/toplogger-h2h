@props(['climberStats'])

<div x-data="{ period: '60d' }" {{ $attributes->class(['px-3 mb-10']) }}>

    <div class="border-b border-gray-200 pb-1 mb-2 flex justify-between items-center">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">Top 10</h2>
        <select x-model="period"
            class="block pl-3 pr-10 py-1.5 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option value="60d" selected>60d</option>
            <option value="all">Alles</option>
        </select>
    </div>

    <div class="flex space-x-2 justify-around sm:justify-items-start">
        @foreach($climberStats as $name => $stats)
            <div class="w-1/2 sm:w-52">
                <div class="flex items-baseline space-x-1">
                    <h2 class="text-xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">{{ $name }}</h2>
                </div>

                <div class="flex flex-col space-y-1" x-data x-show="period == '60d'">
                    @foreach(collect($stats['top_ten_60d'])->sortByDesc(fn ($ascend) => (float) $ascend['grade']) as $ascend)
                        <x-ascend :grade="$ascend['grade_font']"
                                  :flash="$ascend['checks'] == 2"
                                  :color="$ascend['color']"
                                  :description="$ascend['gym_name'] ?? ''"
                                  x-tooltip="{{ $ascend['wall_name'] }}, {{ $ascend['days_ago'] }} dagen geleden"
                        />
                    @endforeach
                </div>

                @php
                    $stats['top_ten_all'] = collect($stats['top_ten_all'])
                                                ->sortByDesc(fn($ascend) => (new \Carbon\Carbon($ascend['date_logged']))->unix())
                                                ->sortByDesc(fn($ascend) => (int)$ascend['checks'])
                                                ->sortByDesc(fn($ascend) => (float)$ascend['climb']['grade'])
                @endphp

                <div class="flex flex-col space-y-1" x-data x-show="period == 'all'" x-cloak>
                    @foreach($stats['top_ten_all'] as $ascend)
                        <x-ascend :grade="$ascend['climb']['grade_font']"
                                  :flash="$ascend['checks'] == 2"
                                  :color="$ascend['climb']['hold_color']"
                                  :description="$ascend['climb']['gym_name']"
                                  x-tooltip="{{ $ascend['climb']['wall_name'] }}, {{ $ascend['days_ago'] }} dagen geleden"
                        />
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
