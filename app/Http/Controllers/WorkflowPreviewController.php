<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\WorkflowPreviewService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WorkflowPreviewController extends Controller
{
    /**
     * Generate workflow preview based on form data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function preview(Request $request): JsonResponse
    {
        try {
            // Log the incoming request for debugging
            Log::info('Workflow preview request received', [
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            // Validate the request
            $validator = Validator::make($request->all(), [
                'form_type' => 'required|in:IOM,Leave',
                'to_department_id' => 'required_if:form_type,IOM|numeric',
                'leave_type' => 'required_if:form_type,Leave|string',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:2000',
                'emergency_level' => 'nullable|string|in:normal,urgent,emergency'
            ]);

            if ($validator->fails()) {
                Log::warning('Workflow preview validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate workflow preview
            $previewData = WorkflowPreviewService::generateWorkflowPreview($request->all());

            if (!$previewData['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $previewData['message'] ?? 'Failed to generate workflow preview'
                ], 400);
            }

            // Log successful preview generation
            Log::info('Workflow preview generated successfully', [
                'user_id' => auth()->id(),
                'form_type' => $request->form_type,
                'to_department_id' => $request->to_department_id,
                'total_steps' => $previewData['total_steps'],
                'estimated_days' => $previewData['estimated_completion_days']
            ]);

            // Ensure proper JSON encoding
            return response()->json([
                'success' => true,
                'data' => $previewData
            ], 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache'
            ]);

        } catch (\Exception $e) {
            Log::error('Workflow preview generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the workflow preview. Please try again.',
                'error_code' => 'PREVIEW_GENERATION_FAILED'
            ], 500, [
                'Content-Type' => 'application/json'
            ]);
        }
    }

    /**
     * Get simplified preview for quick display
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function quickPreview(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'form_type' => 'required|in:IOM,Leave',
                'to_department_id' => 'required_if:form_type,IOM|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request parameters'
                ], 422);
            }

            $previewData = WorkflowPreviewService::generateWorkflowPreview($request->all());

            if (!$previewData['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $previewData['message']
                ], 400);
            }

            // Return simplified data for quick preview
            $quickData = [
                'total_steps' => $previewData['total_steps'],
                'estimated_days' => $previewData['estimated_completion_days'],
                'form_type' => $previewData['form_type'],
                'requires_job_order' => $previewData['requires_job_order'] ?? false,
                'key_approvers' => $this->extractKeyApprovers($previewData['workflow_steps']),
                'workflow_type' => $this->determineWorkflowType($previewData)
            ];

            return response()->json([
                'success' => true,
                'data' => $quickData
            ]);

        } catch (\Exception $e) {
            Log::error('Quick workflow preview failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to generate quick preview'
            ], 500);
        }
    }

    /**
     * Extract key approvers from workflow steps
     */
    private function extractKeyApprovers(array $workflowSteps): array
    {
        $keyApprovers = [];

        foreach ($workflowSteps as $step) {
            if ($step['actor'] !== 'System' && $step['actor'] !== 'You' && !in_array($step['actor'], $keyApprovers)) {
                $keyApprovers[] = [
                    'name' => $step['actor'],
                    'position' => $step['actor_position'],
                    'department' => $step['department']
                ];
            }
        }

        return array_slice($keyApprovers, 0, 3); // Return top 3 key approvers
    }

    /**
     * Determine workflow type based on preview data
     */
    private function determineWorkflowType(array $previewData): string
    {
        if (isset($previewData['target_department']) && strpos($previewData['target_department'], 'PFMO') !== false) {
            return 'Enhanced PFMO Workflow';
        }

        if ($previewData['total_steps'] > 4) {
            return 'Complex Multi-Level Approval';
        }

        if ($previewData['form_type'] === 'Leave') {
            return 'Leave Request Workflow';
        }

        return 'Standard Department Workflow';
    }

    /**
     * Get workflow templates for common scenarios
     *
     * @return JsonResponse
     */
    public function getWorkflowTemplates(): JsonResponse
    {
        try {
            $templates = [
                'iom_same_department' => [
                    'name' => 'IOM - Same Department',
                    'description' => 'Request within your own department',
                    'estimated_steps' => 2,
                    'estimated_days' => 0,
                    'complexity' => 'Simple'
                ],
                'iom_cross_department' => [
                    'name' => 'IOM - Cross Department',
                    'description' => 'Request to another department',
                    'estimated_steps' => 3,
                    'estimated_days' => 2,
                    'complexity' => 'Standard'
                ],
                'iom_pfmo' => [
                    'name' => 'IOM - PFMO Request',
                    'description' => 'Facilities and maintenance request',
                    'estimated_steps' => 6,
                    'estimated_days' => 5,
                    'complexity' => 'Complex'
                ],
                'leave_staff' => [
                    'name' => 'Staff Leave Request',
                    'description' => 'Regular staff leave application',
                    'estimated_steps' => 3,
                    'estimated_days' => 2,
                    'complexity' => 'Standard'
                ],
                'leave_head' => [
                    'name' => 'Department Head Leave',
                    'description' => 'Department head leave application',
                    'estimated_steps' => 4,
                    'estimated_days' => 3,
                    'complexity' => 'Standard'
                ]
            ];

            return response()->json([
                'success' => true,
                'templates' => $templates
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get workflow templates', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load workflow templates'
            ], 500);
        }
    }
}
