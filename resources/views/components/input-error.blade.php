@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'mt-2']) }}>
        @foreach ((array) $messages as $message)
            <div class="flex items-center bg-red-50 border border-red-200 rounded-md px-3 py-2 mb-2">
                <div class="flex-shrink-0">
                    <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-2 text-sm text-red-800">
                    {{ $message }}
                </div>
            </div>
        @endforeach
    </div>
@endif
