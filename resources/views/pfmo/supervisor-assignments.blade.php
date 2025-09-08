@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Supervisor Management</h1>
                    <p class="mt-1 text-sm text-gray-600">Assign supervisors to PFMO sub-departments</p>
                </div>
                <a href="{{ route('pfmo.dashboard') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Sub-departments Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($subDepartments as $subDepartment)
                <div class="bg-white rounded-lg shadow border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $subDepartment->name }}</h3>
                            <span class="text-sm text-gray-500">{{ $subDepartment->subdepartment_code }}</span>
                        </div>

                        <p class="text-sm text-gray-600 mb-4">{{ $subDepartment->description }}</p>

                        {{-- Current Supervisor --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Supervisor</label>
                            @if($subDepartment->supervisor)
                                <div class="flex items-center justify-between bg-blue-50 p-3 rounded-lg">
                                    <div>
                                        <p class="font-medium text-blue-900">
                                            {{ $subDepartment->supervisor->FirstName }} {{ $subDepartment->supervisor->LastName }}
                                        </p>
                                        <p class="text-sm text-blue-600">{{ $subDepartment->supervisor->Emp_No }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('supervisor.remove') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="sub_department_id" value="{{ $subDepartment->id }}">
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-800 text-sm"
                                                onclick="return confirm('Are you sure you want to remove this supervisor?')">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="bg-gray-50 p-3 rounded-lg text-center">
                                    <p class="text-gray-500 text-sm">No supervisor assigned</p>
                                </div>
                            @endif
                        </div>

                        {{-- Assign New Supervisor --}}
                        <form method="POST" action="{{ route('supervisor.assign') }}">
                            @csrf
                            <input type="hidden" name="sub_department_id" value="{{ $subDepartment->id }}">
                            
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $subDepartment->supervisor ? 'Change Supervisor' : 'Assign Supervisor' }}
                            </label>
                            
                            <div class="flex space-x-2">
                                <select name="supervisor_id" 
                                        class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Select Employee</option>
                                    @foreach($availableEmployees as $employee)
                                        @if(!$employee->user || $employee->user->department->dept_code === 'PFMO')
                                            <option value="{{ $employee->Emp_No }}"
                                                    @if($subDepartment->supervisor_id === $employee->Emp_No) selected @endif>
                                                {{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->Emp_No }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                
                                <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                                    {{ $subDepartment->supervisor ? 'Update' : 'Assign' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Instructions --}}
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-blue-900 mb-2">Instructions</h3>
            <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                <li>Each sub-department can have only one supervisor</li>
                <li>An employee can supervise only one sub-department at a time</li>
                <li>Only PFMO employees can be assigned as supervisors</li>
                <li>Supervisors will receive notifications for requests assigned to their sub-departments</li>
                <li>Remove a supervisor before assigning them to a different sub-department</li>
            </ul>
        </div>
    </div>
</div>
@endsection
