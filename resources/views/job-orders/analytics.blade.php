<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Job Order Analytics Dashboard') }}
            </h2>
            <div class="flex space-x-2">
                <select id="dateRange" class="bg-white border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>Last 7 days</option>
                    <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>Last 30 days</option>
                    <option value="90" {{ $dateRange == '90' ? 'selected' : '' }}>Last 90 days</option>
                    <option value="365" {{ $dateRange == '365' ? 'selected' : '' }}>Last year</option>
                </select>
                <a href="{{ route('job-orders.index') }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                    Back to Job Orders
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Overview Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 3a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Jobs</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ collect($analytics['completion_rates']['by_service_type'])->sum('total_jobs') }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Completed</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ collect($analytics['completion_rates']['by_service_type'])->sum('completed_jobs') }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Avg Resolution</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ number_format(collect($analytics['average_resolution_time'])->avg('avg_hours'), 1) }}h
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Avg Rating</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ $analytics['satisfaction_ratings']['overall']->avg_rating ?? 'N/A' }}/5
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completion Rates by Technician -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Completion Rates by Technician</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Technician</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Jobs</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Completed</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Completion Rate</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($analytics['completion_rates']['by_technician'] as $tech)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $tech->username }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $tech->total_jobs }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $tech->completed_jobs }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $tech->completion_rate }}%"></div>
                                            </div>
                                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $tech->completion_rate }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Service Type Breakdown and Workload Distribution -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Service Type Breakdown -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Service Type Breakdown</h3>
                        <div class="space-y-3">
                            @foreach($analytics['service_type_breakdown'] as $service)
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $service->service_type }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">{{ $service->job_count }} jobs ({{ $service->percentage }}%)</span>
                                    </div>
                                    <div class="mt-1 w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $service->percentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Current Workload Distribution -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Workload Distribution</h3>
                        <div class="space-y-3">
                            @foreach($analytics['workload_distribution'] as $staff)
                            <div class="border rounded-lg p-3">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $staff->username }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        Total: {{ $staff->pending_jobs + $staff->in_progress_jobs }}
                                    </span>
                                </div>
                                <div class="flex space-x-2 text-xs">
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                                        Pending: {{ $staff->pending_jobs }}
                                    </span>
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                        In Progress: {{ $staff->in_progress_jobs }}
                                    </span>
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded">
                                        Completed (30d): {{ $staff->completed_jobs }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers -->
            @if($analytics['top_performers']->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Top Performers</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($analytics['top_performers']->take(6) as $index => $performer)
                        <div class="border rounded-lg p-4 text-center {{ $index < 3 ? 'border-yellow-400 bg-yellow-50' : 'border-gray-200' }}">
                            @if($index === 0)
                                <div class="text-yellow-500 text-2xl mb-2">🥇</div>
                            @elseif($index === 1)
                                <div class="text-gray-400 text-2xl mb-2">🥈</div>
                            @elseif($index === 2)
                                <div class="text-orange-600 text-2xl mb-2">🥉</div>
                            @endif
                            <div class="font-semibold text-gray-900">{{ $performer->username }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $performer->completed_jobs }} jobs completed
                            </div>
                            <div class="text-sm text-yellow-600 font-medium">
                                ⭐ {{ number_format($performer->avg_rating, 1) }}/5.0
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    <script>
        // Auto-refresh analytics data
        document.getElementById('dateRange').addEventListener('change', function() {
            const range = this.value;
            window.location.href = `{{ route('job-orders.analytics') }}?range=${range}`;
        });

        // You can add Chart.js integration here for visual charts
    </script>
</x-app-layout>
