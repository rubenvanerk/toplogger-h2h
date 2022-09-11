@extends('layouts.base')

@section('body')
    <div class="min-h-full">
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1>{{ config('app.name') }}</h1>
                        </div>
                    </div>
                    <div class="my-auto" x-data>
                        <x-button x-on:click="window.livewire.emit('clearCache')" wire:loading.attr="disabled">
                            Ververs data
                        </x-button>
                    </div>
                </div>
            </div>
        </nav>

        <div class="py-4">
            <main>
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    @yield('content')

                    @isset($slot)
                        {{ $slot }}
                    @endisset
                </div>
            </main>
        </div>
    </div>
@endsection

