@props(['climberStats'])

@php
    $climber1 = array_shift($climberStats);
    $climber2 = array_shift($climberStats);
@endphp

<div class="grid grid-cols-3 justify-items-center items-center pb-5">
    <div class="mb-1">
        <span class="text-2xl font-bold leading-7 text-gray-800 sm:text-3xl sm:truncate">Ruben</span>
    </div>
    <div></div>
    <div class="mb-1">
        <span class="text-2xl font-bold leading-7 text-gray-800 sm:text-3xl sm:truncate">Wouter</span>
    </div>

    <div>
        <span class=" text-2xl font-extrabold text-indigo-600 sm:text-3xl">
            {{ $climber1['grade_font'] ?? 0 }}
        </span>
        <sup class="font-medium text-xs text-gray-600">+{{ $climber1['grade_progress'] ?? 0 }}%</sup>
    </div>
    <div class="px-3 py-3 text-xs font-medium uppercase tracking-wide text-gray-500">
        60d Grade
    </div>
    <div>
        <span class="text-2xl font-extrabold text-indigo-600 sm:text-3xl">
            {{ $climber2['grade_font'] ?? 0 }}
        </span>
        <sup class="font-medium text-xs text-gray-600">+{{ $climber2['grade_progress'] ?? 0 }}%</sup>
    </div>

    <div>
        <span class=" text-2xl font-extrabold text-indigo-600 sm:text-3xl">{{ $climber1['tops'] }}</span>
    </div>
    <div class="px-3 py-3 text-xs font-medium uppercase tracking-wide text-gray-500">
        Tops
    </div>
    <div>
        <span class=" text-2xl font-extrabold text-indigo-600 sm:text-3xl">{{ $climber2['tops'] }}</span>
    </div>

    <div>
        <span class=" text-2xl font-extrabold text-indigo-600 sm:text-3xl">{{ $climber1['sessionCount'] }}</span>
    </div>
    <div class="px-3 py-3 text-xs font-medium uppercase tracking-wide text-gray-500">
        Sessies
    </div>
    <div>
        <span class=" text-2xl font-extrabold text-indigo-600 sm:text-3xl">{{ $climber2['sessionCount'] }}</span>
    </div>

    <div>
        <span class=" text-2xl font-extrabold text-indigo-600 sm:text-3xl">{{ round($climber1['tops'] / $climber1['sessionCount'], 1) }}</span>
    </div>
    <div class="px-3 py-3 text-xs font-medium uppercase tracking-wide text-gray-500">
        tops/sessie
    </div>
    <div>
        <span class=" text-2xl font-extrabold text-indigo-600 sm:text-3xl">{{ round($climber2['tops'] / $climber2['sessionCount'], 1) }}</span>
    </div>
</div>
