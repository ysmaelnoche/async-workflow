<x-app-layout>
	<x-slot name="header">
		<div class="flex justify-between items-center">
			<h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
				Job Orders
			</h2>
			<div class="text-sm text-gray-600 dark:text-gray-400">
				{{ $jobOrders->total() }} total job orders
			</div>
		</div>
	</x-slot>

	<div class="py-12">
		<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
			<!-- Search Form -->
			<div class="mb-6">
				<form class="flex gap-3" method="GET">
					<div class="flex-1">
						<input name="q" 
							   value="{{ request('q') }}" 
							   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
							   placeholder="Search job orders by number, control number, or requestor..." />
					</div>
					<button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">
						<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
						</svg>
						Search
					</button>
					@if(request('q'))
						<a href="{{ route('job-orders.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors duration-200">
							Clear
						</a>
					@endif
				</form>
			</div>

			<!-- Job Orders Grid -->
			@if($jobOrders->count() > 0)
				<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
					@foreach($jobOrders as $jo)
						<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-shadow duration-200">
							<!-- Card Header -->
							<div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
								<div class="flex justify-between items-start">
									<div>
										<h3 class="text-white font-semibold text-lg">
											{{ $jo->job_order_number }}
										</h3>
										@if($jo->control_number)
											<p class="text-blue-100 text-sm">
												Control #: {{ $jo->control_number }}
											</p>
										@endif
									</div>
									<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
										@if($jo->status === 'Completed') bg-green-100 text-green-800
										@elseif($jo->status === 'In Progress') bg-blue-100 text-blue-800
										@elseif($jo->status === 'Pending') bg-yellow-100 text-yellow-800
										@else bg-gray-100 text-gray-800
										@endif">
										{{ $jo->status }}
									</span>
								</div>
							</div>

							<!-- Card Content -->
							<div class="p-6">
								<div class="space-y-3">
									<div class="flex items-center text-sm">
										<svg class="w-4 h-4 text-gray-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
										</svg>
										<span class="text-gray-900 dark:text-gray-100 font-medium">{{ $jo->requestor_name }}</span>
									</div>
									
									<div class="flex items-center text-sm">
										<svg class="w-4 h-4 text-gray-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
										</svg>
										<span class="text-gray-600 dark:text-gray-400">{{ $jo->department }}</span>
									</div>

									@if(isset($jo->date_created))
										<div class="flex items-center text-sm">
											<svg class="w-4 h-4 text-gray-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
											</svg>
											<span class="text-gray-600 dark:text-gray-400">Created {{ \Carbon\Carbon::parse($jo->date_created)->diffForHumans() }}</span>
										</div>
									@endif
								</div>
							</div>

							<!-- Card Footer -->
							<div class="bg-gray-50 dark:bg-gray-700 px-6 py-4">
								<div class="flex justify-between items-center">
									<div class="flex items-center space-x-2">
										@if($jo->status === 'Completed')
											<span class="inline-flex items-center text-green-600 text-sm font-medium">
												<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
													<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
												</svg>
												Complete
											</span>
										@elseif($jo->status === 'In Progress')
											<span class="inline-flex items-center text-blue-600 text-sm font-medium">
												<svg class="w-4 h-4 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
													<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
													<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
												</svg>
												Working
											</span>
										@else
											<span class="inline-flex items-center text-yellow-600 text-sm font-medium">
												<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
													<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
												</svg>
												{{ $jo->status }}
											</span>
										@endif
									</div>
									
									<a href="{{ route('job-orders.show', ['jobOrder' => $jo->job_order_id]) }}" 
									   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
										View Details
										<svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
										</svg>
									</a>
								</div>
							</div>
						</div>
					@endforeach
				</div>

				<!-- Pagination -->
				<div class="mt-8">
					{{ $jobOrders->withQueryString()->links() }}
				</div>
			@else
				<!-- Empty State -->
				<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
					<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
					</svg>
					<h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-gray-100">No job orders found</h3>
					@if(request('q'))
						<p class="mt-1 text-gray-500 dark:text-gray-400">No job orders match your search criteria.</p>
						<div class="mt-6">
							<a href="{{ route('job-orders.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
								Clear search
							</a>
						</div>
					@else
						<p class="mt-1 text-gray-500 dark:text-gray-400">No job orders have been created yet.</p>
					@endif
				</div>
			@endif
		</div>
	</div>
</x-app-layout>
