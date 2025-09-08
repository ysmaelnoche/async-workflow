<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use App\Models\SubDepartment;

class AssignSupervisorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only PFMO Head can assign supervisors
        $user = auth()->user();
        return $user && 
               $user->department && 
               $user->department->dept_code === 'PFMO' && 
               $user->position === 'Head';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'integer',
                'exists:tb_account,accnt_id',
                function ($attribute, $value, $fail) {
                    $employee = User::with('department')->find($value);
                    if (!$employee || !$employee->department || $employee->department->dept_code !== 'PFMO') {
                        $fail('The selected employee must be a PFMO staff member.');
                    }
                },
            ],
            'sub_department_id' => [
                'required',
                'integer',
                'exists:sub_departments,id',
                function ($attribute, $value, $fail) {
                    // Skip validation if this is a reassignment
                    if (request()->has('reassign') && request()->reassign) {
                        return;
                    }
                    
                    $subDepartment = SubDepartment::find($value);
                    if ($subDepartment && $subDepartment->supervisor_id) {
                        $fail('This sub-department already has a supervisor assigned. Use reassign if you want to replace the current supervisor.');
                    }
                },
            ],
            'reassign' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Please select an employee.',
            'employee_id.exists' => 'The selected employee does not exist.',
            'sub_department_id.required' => 'Please select a sub-department.',
            'sub_department_id.exists' => 'The selected sub-department does not exist.',
        ];
    }
}
