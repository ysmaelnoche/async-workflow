<?php

namespace App\Services;

use App\Models\Department;
use App\Models\User;

class AutomatedRoutingService
{
    /**
     * Get the appropriate destination department and approver based on request type
     */
    public static function getDestinationForRequestType(string $requestType, int $fromDepartmentId)
    {
        $routing = [
            'IOM' => [
                'department_names' => ['PFMO', 'Physical Facilities and Maintenance Office'],
                'fallback_department_id' => 13, // PFMO
                'description' => 'Internal Office Memorandum - routed to PFMO'
            ],
            'Leave' => [
                'department_names' => ['Human Resources', 'HR', 'Personnel'],
                'fallback_department_id' => 14, // Administration (contains HR)
                'description' => 'Leave Request - routed to Human Resources'
            ],
            'Job Order' => [
                'department_names' => ['PFMO', 'Physical Facilities and Maintenance Office'],
                'fallback_department_id' => 13, // PFMO
                'description' => 'Job Order Request - routed to PFMO'
            ],
            'Purchase Request' => [
                'department_names' => ['Procurement', 'Finance', 'Administration'],
                'fallback_department_id' => 14, // Administration
                'description' => 'Purchase Request - routed to Procurement/Finance'
            ],
            'IT Support' => [
                'department_names' => ['Information Technology', 'IT', 'Computer Services'],
                'fallback_department_id' => 15, // College of Computer Studies (fallback)
                'description' => 'IT Support Request - routed to IT Department'
            ],
            'Vehicle Request' => [
                'department_names' => ['PFMO', 'Physical Facilities and Maintenance Office', 'Administration'],
                'fallback_department_id' => 13, // PFMO
                'description' => 'Vehicle Request - routed to PFMO'
            ]
        ];

        if (!isset($routing[$requestType])) {
            // Default routing for unknown request types
            return [
                'department_id' => 14, // Administration as default
                'department_name' => 'Administration',
                'approver_id' => null,
                'description' => 'Unknown request type - routed to Administration'
            ];
        }

        $routingConfig = $routing[$requestType];
        
        // Try to find the target department by name
        $targetDepartment = Department::whereIn('dept_name', $routingConfig['department_names'])->first();
        
        if (!$targetDepartment) {
            // Use fallback department
            $targetDepartment = Department::find($routingConfig['fallback_department_id']);
        }

        // Find the department head/approver for the target department
        $approver = null;
        if ($targetDepartment) {
            $approver = User::where('department_id', $targetDepartment->department_id)
                ->where('position', 'Head')
                ->where('accessRole', 'Approver')
                ->first();
        }

        return [
            'department_id' => $targetDepartment ? $targetDepartment->department_id : $routingConfig['fallback_department_id'],
            'department_name' => $targetDepartment ? $targetDepartment->dept_name : 'Unknown Department',
            'approver_id' => $approver ? $approver->accnt_id : null,
            'description' => $routingConfig['description'],
            'has_approver' => $approver !== null
        ];
    }

    /**
     * Get all available request types with their routing information
     */
    public static function getAvailableRequestTypes()
    {
        return [
            'IOM' => [
                'label' => 'Internal Office Memorandum',
                'description' => 'Communication between departments',
                'icon' => 'fas fa-file-alt',
                'color' => 'blue'
            ],
            'Leave' => [
                'label' => 'Leave Request',
                'description' => 'Annual, Sick, Emergency leave applications',
                'icon' => 'fas fa-calendar-times',
                'color' => 'green'
            ],
            'Job Order' => [
                'label' => 'Job Order Request',
                'description' => 'Maintenance, repairs, and facility requests',
                'icon' => 'fas fa-tools',
                'color' => 'orange'
            ],
            'Purchase Request' => [
                'label' => 'Purchase Request',
                'description' => 'Equipment, supplies, and service purchases',
                'icon' => 'fas fa-shopping-cart',
                'color' => 'purple'
            ],
            'IT Support' => [
                'label' => 'IT Support Request',
                'description' => 'Technical support and IT services',
                'icon' => 'fas fa-laptop',
                'color' => 'teal'
            ],
            'Vehicle Request' => [
                'label' => 'Vehicle Request',
                'description' => 'Official vehicle usage requests',
                'icon' => 'fas fa-car',
                'color' => 'red'
            ]
        ];
    }
}
