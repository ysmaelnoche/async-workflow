@props([
    'type' => 'info', // info, error, warning, success
    'title' => null,
    'closable' => true
])

@php
$typeClasses = [
    'info' => 'bg-blue-50 border-blue-200 text-blue-800',
    'error' => 'bg-red-50 border-red-200 text-red-800',
    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
    'success' => 'bg-green-50 border-green-200 text-green-800',
];

$iconClasses = [
    'info' => 'text-blue-600',
    'error' => 'text-red-600',
    'warning' => 'text-yellow-600',
    'success' => 'text-green-600',
];

$icons = [
    'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    'error' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
    'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
    'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border p-4 mb-4 ' . $typeClasses[$type]]) }} x-data="{ show: true }" x-show="show" x-transition>
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 {{ $iconClasses[$type] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$type] }}"></path>
            </svg>
        </div>
        <div class="ml-3 flex-1">
            @if($title)
                <h3 class="text-sm font-medium mb-1">{{ $title }}</h3>
            @endif
            <div class="text-sm">
                {{ $slot }}
            </div>
        </div>
        @if($closable)
            <div class="ml-auto pl-3">
                <button @click="show = false" class="inline-flex rounded-md p-1.5 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        @endif
    </div>
</div>
