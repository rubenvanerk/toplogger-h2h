<div class="mx-2">
    <div class="h-10">
        <span wire:loading>Loading...</span>
    </div>

    <x-button wire:click="$set('userId', '7163205870')" class="{{ $userId == '7163205870' ? 'font-bold' : '' }}">Ruben</x-button>
    <x-button wire:click="$set('userId', '2943357766')" class="{{ $userId == '2943357766' ? 'font-bold' : '' }}">Wouter</x-button>

    <br><br>

    <x-button wire:click="$set('gymId', 8)" class="{{ $gymId == 8 ? 'font-bold' : '' }}">Bruut</x-button>
    <x-button wire:click="$set('gymId', 3)" class="{{ $gymId == 3 ? 'font-bold' : '' }}">Monk</x-button>
    <x-button wire:click="$set('gymId', 123)" class="{{ $gymId == 123 ? 'font-bold' : '' }}">Neoliet</x-button>
    <x-button wire:click="$set('gymId', 131)" class="{{ $gymId == 131 ? 'font-bold' : '' }}">MN Dordt</x-button>

    @foreach($ascendsByDate as $date => $ascends)
        <h3 class="text-lg mt-2">{{ $date }}</h3>
        <ol>
            @foreach($ascends as $ascend)
                <li class="ml-3 {{ $ascend->checks == 2 ? 'text-amber-500' : '' }}"> {{ $loop->index + 1 }}. {{ $ascend->climb->grade }}</li>
            @endforeach
        </ol>
    @endforeach
</div>
