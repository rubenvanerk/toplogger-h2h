<div class="px-3 mb-4">

    <div class="border-b border-gray-200 pb-1 mb-2">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">Top 10</h2>
    </div>

    <div class="flex space-x-2 justify-around sm:justify-items-start">
        @foreach($climberStats as $userId => $stats)
            <div class="w-1/2 sm:w-52">
                <div class="flex items-baseline space-x-1">
                    <h2 class="text-xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">{{ $this->climberIds[$userId] }}</h2>
                </div>

                <div class="flex flex-col space-y-1">
                    @foreach($stats['top_ten']->sortByDesc(fn ($ascend) => (float) $ascend->grade) as $ascend)
                        <x-ascend :grade="$ascend->grade_font"
                                  :flash="$ascend->checks == 2"
                                  :color="$ascend->color"
                                  :wall="$ascend->wall_name"
                        />
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
