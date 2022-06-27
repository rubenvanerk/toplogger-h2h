<div class="w-screen max-w-xl mx-auto" x-data="{ currentTab: 'stats' }">

    <div wire:loading.class.remove="hidden" class="hidden">
        <div class="text-gray-800 mb-3 w-100 text-center animate-pulse">Data aan het verversen...</div>
    </div>

    <nav class="flex space-x-2 justify-around pb-4" aria-label="Tabs">
        <!-- Current: "bg-indigo-100 text-indigo-700", Default: "text-gray-500 hover:text-gray-700" -->
        <a href="#" class="px-3 py-2 font-medium text-sm rounded-md"
            x-on:click="currentTab = 'stats'" :class="currentTab == 'stats' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700'">
            Stats & top 10
        </a>

        <a href="#" class="px-3 py-2 font-medium text-sm rounded-md"
           x-on:click="currentTab = 'sessions'" :class="currentTab == 'sessions' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700'">
            Sessies
        </a>

        <a href="#" class="px-3 py-2 font-medium text-sm rounded-md"
           x-on:click="currentTab = 'new'" :class="currentTab == 'new' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700'">
            Nieuw
        </a>

        <a href="#" class="px-3 py-2 font-medium text-sm rounded-md"
           x-on:click="currentTab = 'chart'" :class="currentTab == 'chart' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700'">
            Chart
        </a>
    </nav>


    <x-stats :climberStats="$climberStats" x-show="currentTab == 'stats'"/>

    <x-top-ten :climberStats="$climberStats" x-show="currentTab == 'stats'"/>

    <x-chart :chartData="$chartData" :strengthHistory="$strengthHistory" x-show="currentTab == 'chart'" x-cloak/>

    <x-new-climbs :gyms="$gyms" x-show="currentTab == 'new'" x-cloak/>

    <x-ascends-by-date :ascendsByDate="$ascendsByDate" x-show="currentTab == 'sessions'" x-cloak/>

</div>
