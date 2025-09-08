<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
			Job Order Feedback
		</h2>
	</x-slot>

	<div class="py-6">
		<div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
				<form method="POST" action="{{ route('job-orders.feedback', ['jobOrder' => $jobOrder->id]) }}">
					@csrf
					<div class="mb-4">
						<label class="flex items-center gap-2">
							<input type="checkbox" name="job_completed" value="1" class="rounded">
							<span class="text-sm">Job Completed</span>
						</label>
					</div>
					<div class="mb-4">
						<label class="flex items-center gap-2">
							<input type="checkbox" name="for_further_action" value="1" class="rounded">
							<span class="text-sm">For Further Action</span>
						</label>
					</div>
					<div class="mb-4">
						<label class="block text-sm mb-1">Comments</label>
						<textarea name="requestor_comments" class="w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-200" rows="4"></textarea>
					</div>
					<button class="px-4 py-2 bg-green-600 text-white rounded">Submit Feedback</button>
				</form>
			</div>
		</div>
	</div>
</x-app-layout>
