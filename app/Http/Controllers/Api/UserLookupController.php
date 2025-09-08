<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserLookupController extends Controller
{
    /**
     * Lookup user by username
     */
    public function lookup(Request $request): JsonResponse
    {
        $username = $request->input('username');

        if (empty($username) || strlen($username) < 2) {
            return response()->json([
                'found' => false,
                'message' => 'Username too short'
            ]);
        }

        $user = User::with('employeeInfo')
            ->where('username', $username)
            ->first();

        if (!$user) {
            return response()->json([
                'found' => false,
                'message' => 'User not found'
            ]);
        }

        $employeeInfo = $user->employeeInfo;
        $fullName = '';

        if ($employeeInfo) {
            $fullName = trim(
                ($employeeInfo->Titles ? $employeeInfo->Titles . ' ' : '') .
                $employeeInfo->FirstName . ' ' .
                ($employeeInfo->MiddleName ? $employeeInfo->MiddleName . ' ' : '') .
                $employeeInfo->LastName .
                ($employeeInfo->Suffix ? ' ' . $employeeInfo->Suffix : '')
            );
        }

        // Generate avatar URL (you can customize this based on your avatar system)
        $avatarUrl = $this->generateAvatarUrl($user, $employeeInfo);
        return response()->json([
            'found' => true,
            'user' => [
                'username' => $user->username,
                'employee_number' => $user->Emp_No,
                'full_name' => $fullName,
                'position' => $user->position,
                'department' => $user->department->dept_name ?? 'Unknown',
                'avatar_url' => $avatarUrl
            ]
        ]);
    }

    /**
     * Generate avatar URL for user
     */
    private function generateAvatarUrl(User $user, $employeeInfo): string
    {
        // Check if user has a profile photo in storage
        $photoPath = storage_path('app/public/avatars/' . $user->username . '.jpg');
        if (file_exists($photoPath)) {
            return asset('storage/avatars/' . $user->username . '.jpg');
        }

        // Check for PNG version
        $photoPathPng = storage_path('app/public/avatars/' . $user->username . '.png');
        if (file_exists($photoPathPng)) {
            return asset('storage/avatars/' . $user->username . '.png');
        }

        // Fallback to initials avatar
        $initials = '';
        if ($employeeInfo) {
            $initials = strtoupper(
                substr($employeeInfo->FirstName, 0, 1) .
                substr($employeeInfo->LastName, 0, 1)
            );
        } else {
            $initials = strtoupper(substr($user->username, 0, 2));
        }

        // Generate avatar using UI Avatars service
        return "https://ui-avatars.com/api/?name=" . urlencode($initials) .
            "&size=80&background=3b82f6&color=ffffff&bold=true&rounded=true";
    }
}
