<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Employee Information') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Import Section --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-gray-100">Import Employees from Excel</h3>
                    
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

            {{-- Employee Information Section --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($employees->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No employees found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Import employees using the Excel import feature.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="w-20 px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Emp #</th>
                                        <th scope="col" class="w-16 px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                                        <th scope="col" class="w-48 px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="w-56 px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="w-32 px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Username</th>
                                        <th scope="col" class="w-32 px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                                        <th scope="col" class="w-24 px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="w-24 px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Added</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($employees as $employee)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-3 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $employee->Emp_No }}
                                            </td>
                                            <td class="px-2 py-3 text-xs text-gray-900 dark:text-gray-100" title="{{ $employee->Titles }}">
                                                {{ Str::limit($employee->Titles, 10) }}
                                            </td>
                                            <td class="px-3 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                <div class="font-medium">{{ $employee->LastName }} {{ $employee->Suffix }}, {{ $employee->FirstName }}</div>
                                                @if($employee->MiddleName)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($employee->MiddleName, 15) }}</div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                @if($employee->Email)
                                                    <a href="mailto:{{ $employee->Email }}" 
                                                       class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                                       title="{{ $employee->Email }}">
                                                        {{ Str::limit($employee->Email, 25) }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                @if($employee->user && $employee->user->username)
                                                    <span class="font-medium" title="{{ $employee->user->username }}">
                                                        {{ Str::limit($employee->user->username, 12) }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-2 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                @if($employee->user && $employee->user->department)
                                                    <span class="px-1 inline-flex text-xs leading-4 font-semibold rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                                          title="{{ $employee->user->department->dept_name }}">
                                                        {{ Str::limit($employee->user->department->dept_name, 8) }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-2 py-3">
                                                @if($employee->user && $employee->user->status)
                                                    <span class="px-1 inline-flex text-xs leading-4 font-semibold rounded
                                                        @if(strtolower($employee->user->status) === 'active')
                                                            bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                        @elseif(strtolower($employee->user->status) === 'inactive')
                                                            bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                        @else
                                                            bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                                        @endif
                                                    ">
                                                        {{ ucfirst($employee->user->status) }}
                                                    </span>
                                                @else
                                                    <span class="px-1 inline-flex text-xs leading-4 font-semibold rounded bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                        No Acc
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-2 py-3 text-xs text-gray-900 dark:text-gray-100" title="{{ $employee->created_at->format('F j, Y') }}">
                                                {{ $employee->created_at->format('M j') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $employees->links() }}
                        </div>
                    @endif

                    {{-- Summary Information --}}
                    <div class="mt-6 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                            <span>
                                @if(!$employees->isEmpty())
                                    Showing <strong class="font-medium text-gray-900 dark:text-gray-100">{{ $employees->firstItem() }}</strong> 
                                    to <strong class="font-medium text-gray-900 dark:text-gray-100">{{ $employees->lastItem() }}</strong> 
                                    of <strong class="font-medium text-gray-900 dark:text-gray-100">{{ $employees->total() }}</strong> 
                                    {{ Str::plural('employee', $employees->total()) }}
                                @else
                                    No employees found
                                @endif
                            </span>
                            @if(!$employees->isEmpty())
                                <span>
                                    Page {{ $employees->currentPage() }} of {{ $employees->lastPage() }}
                                </span>
                            @endif
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
