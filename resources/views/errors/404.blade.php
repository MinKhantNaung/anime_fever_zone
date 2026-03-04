@extends('layouts.blade-app')

@section('description', 'Page not found')

@section('content')
    <div class="container mx-auto px-4 flex flex-col items-center justify-center min-h-[60vh] text-center">
        <h1 class="text-6xl sm:text-8xl font-bold text-gray-300 select-none">404</h1>
        <p class="mt-4 text-xl sm:text-2xl text-gray-700 font-medium">
            Page not found
        </p>
        <p class="mt-2 text-gray-600 max-w-md">
            Sorry, we couldn't find the page you're looking for. It may have been moved or doesn't exist.
        </p>
        <a href="{{ route('home') }}" wire:navigate.hover
            class="mt-8 inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-[#9926f0] to-[#d122e3] text-white font-semibold rounded-lg shadow-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#9926f0] transition-opacity">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Back to home
        </a>
    </div>
@endsection
