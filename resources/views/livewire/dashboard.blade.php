<div class="w-screen max-w-xl mx-auto">

    <div wire:loading.class.remove="hidden" class="hidden">
        <div class="text-gray-800 mb-3 w-100 text-center animate-pulse">Data aan het verversen...</div>
    </div>

    <x-stats :climberStats="$climberStats"/>

    <x-top-ten :climberStats="$climberStats"/>

    <x-chart :chartData="$chartData"/>

    <x-new-climbs :gyms="$gyms"/>

    <x-ascends-by-date :ascendsByDate="$ascendsByDate"/>

</div>
