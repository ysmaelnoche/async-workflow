<?php

namespace App\Http\Controllers;

use App\Models\SubDepartment;
use App\Models\EmployeeInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorAssignmentController extends Controller
{
    public function index()
    {
        // Check if user is PFMO Head
        if (Auth::user()->department->dept_code !== 'PFMO' || Auth::user()->position !== 'Head') {
            abort(403, 'Unauthorized. Only PFMO Head can manage supervisors.');
        }

        $subDepartments = SubDepartment::with('supervisor')->get();
        $availableEmployees = EmployeeInfo::whereHas('user', function($query) {
            $query->whereHas('department', function($deptQuery) {
                $deptQuery->where('dept_code', 'PFMO');
            });
        })->get();

        return view('pfmo.supervisor-assignments', compact('subDepartments', 'availableEmployees'));
    }

    public function assign(Request $request)
    {
        // Check if user is PFMO Head
        if (Auth::user()->department->dept_code !== 'PFMO' || Auth::user()->position !== 'Head') {
            abort(403, 'Unauthorized. Only PFMO Head can assign supervisors.');
        }

        $request->validate([
            'sub_department_id' => 'required|exists:sub_departments,id',
            'supervisor_id' => 'required|exists:tb_employeeinfo,Emp_No'
        ]);

        $subDepartment = SubDepartment::findOrFail($request->sub_department_id);
        
        // Check if employee is already assigned as supervisor elsewhere
        $existingAssignment = SubDepartment::where('supervisor_id', $request->supervisor_id)
            ->where('id', '!=', $request->sub_department_id)
            ->first();

        if ($existingAssignment) {
            return back()->withErrors([
                'supervisor_id' => 'This employee is already assigned as supervisor to ' . $existingAssignment->name
            ]);
        }

        $subDepartment->supervisor_id = $request->supervisor_id;
        $subDepartment->save();

        return back()->with('success', 'Supervisor assigned successfully to ' . $subDepartment->name);
    }

    public function remove(Request $request)
    {
        // Check if user is PFMO Head
        if (Auth::user()->department->dept_code !== 'PFMO' || Auth::user()->position !== 'Head') {
            abort(403, 'Unauthorized. Only PFMO Head can remove supervisors.');
        }

        $request->validate([
            'sub_department_id' => 'required|exists:sub_departments,id'
        ]);

        $subDepartment = SubDepartment::findOrFail($request->sub_department_id);
        $subDepartment->supervisor_id = null;
        $subDepartment->save();

        return back()->with('success', 'Supervisor removed from ' . $subDepartment->name);
    }
}
