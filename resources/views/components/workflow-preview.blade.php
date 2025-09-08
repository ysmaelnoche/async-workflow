@props(['previewData' => null, 'isModal' => false])

@if($previewData && $previewData['success'])
<div class="{{ $isModal ? 'p-6' : 'bg-white rounded-lg shadow-sm border p-6' }}">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-semibold text-gray-900">
                {{ $previewData['form_type'] }} Workflow Preview
            </h3>
            <p class="text-sm text-gray-600 mt-1">
                Review the complete approval process before submitting your request
            </p>
        </div>
        <div class="text-right">
            <div class="text-2xl font-bold text-blue-600">{{ $previewData['total_steps'] }}</div>
            <div class="text-sm text-gray-500">Total Steps</div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        {{-- Estimated Time --}}
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <div class="text-2xl font-bold text-blue-600">
                        {{ $previewData['estimated_completion_days'] == 0 ? 'Same Day' : $previewData['estimated_completion_days'] . ' Days' }}
                    </div>
                    <div class="text-sm text-blue-700">Estimated Completion</div>
                </div>
            </div>
        </div>

        {{-- Target Department --}}
        @if(isset($previewData['target_department']))
        <div class="bg-green-50 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <div>
                    <div class="text-lg font-semibold text-green-700 truncate">
                        {{ $previewData['target_department'] }}
                    </div>
                    <div class="text-sm text-green-600">Target Department</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Special Features --}}
        <div class="bg-purple-50 rounded-lg p-4">
            <div class="flex items-center">
                @if($previewData['requires_job_order'] ?? false)
                    <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <div>
                        <div class="text-lg font-semibold text-purple-700">Job Order</div>
                        <div class="text-sm text-purple-600">Will be created</div>
                    </div>
                @else
                    <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <div class="text-lg font-semibold text-purple-700">Standard</div>
                        <div class="text-sm text-purple-600">Approval Process</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Workflow Steps Timeline --}}
    <div class="space-y-4">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">Workflow Steps</h4>
        
        @foreach($previewData['workflow_steps'] as $index => $step)
        <div class="flex items-start space-x-4">
            {{-- Step Number & Icon --}}
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ 
                    $step['status'] === 'current' ? 'bg-blue-600 text-white' : 
                    ($step['status'] === 'completed' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-600') 
                }}">
                    @if($step['status'] === 'completed')
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    @else
                        <span class="text-sm font-semibold">{{ $step['step_number'] }}</span>
                    @endif
                </div>
                
                {{-- Connector Line --}}
                @if(!$loop->last)
                <div class="w-0.5 h-12 bg-gray-300 ml-5 mt-2"></div>
                @endif
            </div>

            {{-- Step Content --}}
            <div class="flex-1 min-w-0 pb-8">
                <div class="flex items-center justify-between">
                    <h5 class="text-lg font-medium text-gray-900">{{ $step['title'] }}</h5>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                        $step['status'] === 'current' ? 'bg-blue-100 text-blue-800' : 
                        ($step['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') 
                    }}">
                        {{ ucfirst($step['status']) }}
                    </span>
                </div>
                
                <p class="text-sm text-gray-600 mt-1">{{ $step['description'] }}</p>
                
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    {{-- Actor --}}
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">
                            <span class="font-medium">{{ $step['actor'] }}</span>
                            @if($step['actor_position'] && $step['actor_position'] !== 'Unknown')
                                <span class="text-gray-500">({{ $step['actor_position'] }})</span>
                            @endif
                        </span>
                    </div>
                    
                    {{-- Department --}}
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">{{ $step['department'] }}</span>
                    </div>
                    
                    {{-- Duration --}}
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">{{ $step['estimated_duration'] }}</span>
                    </div>
                </div>
                
                {{-- Action Badge --}}
                @if($step['action'])
                <div class="mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-indigo-100 text-indigo-800">
                        {{ $step['action'] }}
                    </span>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Footer Actions --}}
    <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Preview generated at {{ \Carbon\Carbon::parse($previewData['preview_generated_at'])->format('M j, Y g:i A') }}
            </div>
            
            @if(!$isModal)
            <div class="flex space-x-3">
                <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zM5 14H4v-3h1v3zm6 0v2H9v-2h2zM7 14h6v-2H7v2z" clip-rule="evenodd"></path>
                    </svg>
                    Print Preview
                </button>
                
                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    Continue to Submit
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

@else
{{-- Error State --}}
<div class="{{ $isModal ? 'p-6' : 'bg-white rounded-lg shadow-sm border p-6' }}">
    <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M34 14l-10 10m0 0l-10-10m10 10V4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Preview Not Available</h3>
        <p class="mt-1 text-sm text-gray-500">
            {{ $previewData['message'] ?? 'Unable to generate workflow preview at this time.' }}
        </p>
    </div>
</div>
@endif
