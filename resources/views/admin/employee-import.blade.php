<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Import') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-gray-100">Import Employees from Excel</h3>
                    
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.employee_import') }}" method="POST" enctype="multipart/form-data" id="employeeImportForm">
                        @csrf
                        <input type="file" name="employee_excel" id="employeeExcelInput" accept=".xlsx,.xls" style="display:none" onchange="document.getElementById('employeeImportForm').submit();">
                        <button type="button" onclick="document.getElementById('employeeExcelInput').click();" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Import Employees (ETL)
                        </button>
                    </form>

                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Instructions:</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 list-disc list-inside space-y-1">
                            <li>Select an Excel file (.xlsx or .xls) containing employee data</li>
                            <li>The file will be automatically uploaded and processed</li>
                            <li>Existing employees will be updated with new information</li>
                            <li>New employees will be created with default credentials</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
