@props(['ascendsByDate'])

<div {{ $attributes->class(['flex flex-col space-y-10 px-3']) }}>
    @foreach($ascendsByDate as $date => $ascendsByUser)
        @php $firstAscend = $ascendsByUser->first()->first() @endphp

        <div>
            <div class="border-b border-gray-200 pb-1 mb-2">
                @php
                    $date = \Carbon\Carbon::createFromFormat('Y-m-d', $date)->locale('nl');
                @endphp
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    {{ ucfirst($date->isoFormat('dddd')) }} {{ $date->isoFormat('LL') }}
                </h2>
                <h4 class="text-gray-700">{{ $firstAscend->climb->gym_name }}, {{ $firstAscend->climb->gym_city }}</h4>
            </div>

            <div class="flex space-x-2 justify-around sm:justify-items-start">
                @foreach($ascendsByUser as $name => $ascends)
                    <div class="w-1/2 sm:w-52">
                        <div class="flex items-baseline space-x-1">
                            <h2 class="text-xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">{{ $name }}</h2>
                            <h4 class="text-sm text-gray-600">(totaal: {{ $ascends->count() }})</h4>
                        </div>

                        <div class="flex flex-col space-y-1">
                            @foreach($ascends as $ascend)
                                <x-ascend :grade="$ascend->climb->grade_font"
                                          :flash="(int)$ascend->checks === 2"
                                          :color="$ascend->climb->hold_color"
                                          :description="$ascend->climb->wall_name"
                                          :isRepeat="$ascend->is_repeat ?? false"
                                />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>


