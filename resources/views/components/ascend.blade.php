@props(['grade', 'flash', 'color', 'secondaryColor' => null, 'description' => '', 'isRepeat' => false])

<li {{ $attributes->class('relative col-span-1 flex shadow-sm rounded-md') }}>
    <div class="flex-shrink-0 flex items-center justify-center w-10 text-white text-sm font-bold rounded-l-md text-shadow"
         style="@if($secondaryColor) background: linear-gradient(to left top, {{ $secondaryColor }} 50%, {{ $color }} 50%); @else background-color: {{ $color }}; @endif">
        {{ $grade }}
    </div>
    <div class="flex-1 flex items-center justify-between border-t border-r border-b border-gray-200 bg-white rounded-r-md truncate">
        <div class="flex-1 px-2 py-0.5 text-sm truncate">
            <span class="text-gray-500 font-medium">
                {{ $description }}
            </span>
        </div>
        @if($flash)
            <x-icons.lightning-bolt class="mr-1 w-4 h-4 text-amber-500"/>
        @elseif($isRepeat)
            <x-icons.refresh class="mr-1 w-4 h-4 text-gray-500"/>
        @endif
    </div>
</li>
