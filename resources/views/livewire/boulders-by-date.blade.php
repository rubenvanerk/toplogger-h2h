<div class="w-screen px-2">
    <div wire:loading class="text-gray-800 mb-3 animate-pulse">Data aan het verversen...</div>

    <div class="flex flex-col space-y-10">
        @foreach($ascendsByDate as $date => $ascendsByUser)
            <div>

                <div class="border-b border-gray-200 pb-1 mb-2">
                    @php
                        $date = \Carbon\Carbon::createFromFormat('Y-m-d', $date)->locale('nl');
                    @endphp
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">{{ ucfirst($date->isoFormat('dddd')) }} {{ $date->isoFormat('LL') }}</h2>
                    <h4 class="text-sm text-gray-600">{{ $ascendsByUser->first()->first()->climb->gym_name }}</h4>
                </div>

                <div class="flex space-x-2 justify-around sm:justify-items-start">
                    @foreach($ascendsByUser as $userId => $ascends)
                        <div class="w-1/2 sm:w-52">
                            <div class="flex items-baseline space-x-1">
                                <h2 class="text-xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">{{ $this->climberIds[$userId] }}</h2>
                                <h4 class="text-sm text-gray-600">(totaal: {{ $ascends->count() }})</h4>
                            </div>

                            <div class="flex flex-col space-y-1">
                                @foreach($ascends as $ascend)
                                    <x-ascend :grade="$ascend->climb->grade"
                                              :flash="$ascend->checks == 2"
                                              :color="$ascend->climb->hold_color"
                                              :wall="$ascend->climb->wall_name"
                                    />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

</div>

