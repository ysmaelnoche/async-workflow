@extends('layouts.app')

@section('title', 'Pending Job Order Feedback')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                <i class="fas fa-comment-dots mr-2 text-blue-600"></i>
                Pending Job Order Feedback
            </h1>
            <p class="text-gray-600">
                Please provide feedback for completed job orders to continue submitting new IOM requests.
            </p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-info-circle mr-2"></i>
                {{ session('info') }}
            </div>
        @endif

        @if($pendingJobOrders->count() > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-yellow-800">Feedback Required</h3>
                        <p class="text-yellow-700 text-sm">
                            You have {{ $pendingJobOrders->count() }} completed job order(s) requiring feedback. 
                            New IOM requests are blocked until all feedback is submitted.
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                @foreach($pendingJobOrders as $jobOrder)
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">
                                        Job Order #{{ $jobOrder->job_order_number }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Form ID: {{ $jobOrder->form_id }} | Completed: {{ $jobOrder->date_completed?->format('M j, Y') }}
                                    </p>
                                </div>
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    Completed
                                </span>
                            </div>

                            <div class="mb-4">
                                <h4 class="font-medium text-gray-700 mb-2">Request Description:</h4>
                                <p class="text-gray-600 text-sm">{{ $jobOrder->request_description }}</p>
                            </div>

                            @if($jobOrder->findings)
                                <div class="mb-4">
                                    <h4 class="font-medium text-gray-700 mb-2">Findings:</h4>
                                    <p class="text-gray-600 text-sm">{{ $jobOrder->findings }}</p>
                                </div>
                            @endif

                            @if($jobOrder->actions_taken)
                                <div class="mb-4">
                                    <h4 class="font-medium text-gray-700 mb-2">Actions Taken:</h4>
                                    <p class="text-gray-600 text-sm">{{ $jobOrder->actions_taken }}</p>
                                </div>
                            @endif

                            <div class="flex justify-end">
                                <a href="{{ route('job-orders.show', $jobOrder->job_order_id) }}?openModal=complete" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                    <i class="fas fa-star mr-2"></i>
                                    Provide Feedback
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                <i class="fas fa-check-circle text-green-600 text-4xl mb-4"></i>
                <h3 class="text-lg font-semibold text-green-800 mb-2">All Feedback Complete!</h3>
                <p class="text-green-700">
                    You have no pending job order feedback. You can continue submitting new IOM requests.
                </p>
                <div class="mt-4">
                    <a href="{{ route('request.create') }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Submit New Request
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
