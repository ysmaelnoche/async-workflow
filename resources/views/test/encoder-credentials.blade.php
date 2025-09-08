@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4">
                <h1 class="text-2xl font-bold">🔐 Encoder Authentication Credentials</h1>
                <p class="text-blue-100 mt-1">Auto-filled names and their corresponding PINs for testing</p>
            </div>
            
            <div class="p-6">
                <!-- Dean Credentials -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Dean Credentials
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="font-medium text-blue-800">College of Computer Studies</h3>
                            <p class="text-sm text-blue-600 mt-1">Dean Regie Ellana</p>
                            <p class="text-lg font-mono font-bold text-blue-800 mt-2">PIN: 1234</p>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="font-medium text-blue-800">College of Engineering</h3>
                            <p class="text-sm text-blue-600 mt-1">Dr. John Rodriguez</p>
                            <p class="text-lg font-mono font-bold text-blue-800 mt-2">PIN: 5678</p>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="font-medium text-blue-800">College of Business</h3>
                            <p class="text-sm text-blue-600 mt-1">Dr. Ana Dela Cruz</p>
                            <p class="text-lg font-mono font-bold text-blue-800 mt-2">PIN: 9012</p>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="font-medium text-blue-800">College of Arts and Sciences</h3>
                            <p class="text-sm text-blue-600 mt-1">Dr. Michael Johnson</p>
                            <p class="text-lg font-mono font-bold text-blue-800 mt-2">PIN: 3456</p>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="font-medium text-blue-800">College of Education</h3>
                            <p class="text-sm text-blue-600 mt-1">Dr. Sarah Williams</p>
                            <p class="text-lg font-mono font-bold text-blue-800 mt-2">PIN: 7890</p>
                        </div>
                    </div>
                </div>
                
                <!-- Secretary Credentials -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-0.257-0.257A6 6 0 1118 8zM2 8a6 6 0 1010.743 5.743L12 14l-0.257-0.257A6 6 0 112 8zm8-4a4 4 0 100 8 4 4 0 000-8z" clip-rule="evenodd"></path>
                        </svg>
                        Secretary Credentials
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h3 class="font-medium text-green-800">College of Computer Studies</h3>
                            <p class="text-sm text-green-600 mt-1">Mr. Andro Philip Banag</p>
                            <p class="text-lg font-mono font-bold text-green-800 mt-2">PIN: 2468</p>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h3 class="font-medium text-green-800">College of Engineering</h3>
                            <p class="text-sm text-green-600 mt-1">Ms. Carmen Lopez</p>
                            <p class="text-lg font-mono font-bold text-green-800 mt-2">PIN: 1357</p>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h3 class="font-medium text-green-800">College of Business</h3>
                            <p class="text-sm text-green-600 mt-1">Ms. Lisa Chen</p>
                            <p class="text-lg font-mono font-bold text-green-800 mt-2">PIN: 3691</p>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h3 class="font-medium text-green-800">College of Arts and Sciences</h3>
                            <p class="text-sm text-green-600 mt-1">Ms. Rachel Brown</p>
                            <p class="text-lg font-mono font-bold text-green-800 mt-2">PIN: 2580</p>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h3 class="font-medium text-green-800">College of Education</h3>
                            <p class="text-sm text-green-600 mt-1">Ms. Anna Garcia</p>
                            <p class="text-lg font-mono font-bold text-green-800 mt-2">PIN: 1470</p>
                        </div>
                    </div>
                </div>
                
                <!-- Instructions -->
                <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-yellow-800 mb-2">🎯 How the Signature System Works</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-green-100 border border-green-300 rounded p-3">
                            <h4 class="font-semibold text-green-800">👑 Dean Encoding:</h4>
                            <ul class="list-disc list-inside text-green-700 text-sm mt-1 space-y-1">
                                <li>Auto-approved with Dean's digital signature</li>
                                <li>Status: "Approved" immediately</li>
                                <li>Routes directly to PFMO</li>
                                <li>Complete authority to sign</li>
                            </ul>
                        </div>
                        <div class="bg-blue-100 border border-blue-300 rounded p-3">
                            <h4 class="font-semibold text-blue-800">📝 Secretary Encoding:</h4>
                            <ul class="list-disc list-inside text-blue-700 text-sm mt-1 space-y-1">
                                <li>Status: "Pending Dean Approval"</li>
                                <li>NO signature authority</li>
                                <li>Dean must review and sign separately</li>
                                <li>Only then routes to PFMO</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3 p-3 bg-gray-100 border border-gray-300 rounded">
                        <p class="text-sm text-gray-700"><strong>Key Point:</strong> Only the Dean has official signature authority. Secretaries prepare documents, but Dean must always approve and sign before requests proceed.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
