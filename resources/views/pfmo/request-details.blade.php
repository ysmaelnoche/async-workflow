{{-- This view redirects to the proper approval view with all functionality --}}
@php
    // Redirect to the proper approvals.show route to maintain consistency
    $redirectUrl = route('approvals.show', $request->form_id);
@endphp

<script>
    // Immediate redirect to the proper approval view
    window.location.href = "{{ $redirectUrl }}";
</script>

{{-- Fallback if JavaScript is disabled --}}
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full bg-white shadow-lg rounded-lg p-6">
        <div class="text-center">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Redirecting...</h2>
            <p class="text-gray-600 mb-4">You are being redirected to the proper approval view.</p>
            <a href="{{ $redirectUrl }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Click here if not redirected automatically
            </a>
        </div>
    </div>
</div>
