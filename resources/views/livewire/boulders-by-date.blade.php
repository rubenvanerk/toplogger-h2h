<div class="mx-2">
    <div class="h-10">
        <x-button wire:click="refresh" wire:loading.attr="disabled">Refresh</x-button>
        <span wire:loading>Loading...</span>
    </div>


    <div class="flex flex-col space-y-6">
    @foreach($ascendsByDate as $date => $ascendsByUser)
        <div>
            <h3 class="text-xl">{{ $date }} - {{ $ascendsByUser->first()->first()->climb->gym_name }}</h3>
            <div class="flex space-x-4">
                @foreach($ascendsByUser as $userId => $ascends)
                    <div class="w-32">
                        <h4 class="text-lg">{{ $this->climberIds[$userId] }}</h4>

                        <div>
                            <ol class="list-decimal ml-10" role="list">
                                @foreach($ascends as $ascend)
                                    <li class="{{ $ascend->checks == 2 ? 'text-amber-500' : '' }}">
                                        {{ $ascend->climb->grade }}
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
    </div>

</div>

