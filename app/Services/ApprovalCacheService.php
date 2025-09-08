<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApprovalCacheService
{
    /**
     * Cache prefixes for different types of approval data
     */
    const CACHE_PREFIX_USER_REQUESTS = 'user_requests_';
    const CACHE_PREFIX_DEPARTMENT_REQUESTS = 'dept_requests_';
    const CACHE_PREFIX_APPROVAL_STATS = 'approval_stats_';
    const CACHE_PREFIX_PFMO_DASHBOARD = 'pfmo_dashboard';
    const CACHE_PREFIX_USER_PERMISSIONS = 'user_permissions_';

    /**
     * Default cache duration (in minutes)
     */
    const DEFAULT_CACHE_DURATION = 30;

    /**
     * Clear all approval-related caches
     * 
     * @return void
     */
    public static function clearAllApprovalCaches(): void
    {
        try {
            // Clear specific cache patterns
            self::clearCacheByPattern(self::CACHE_PREFIX_USER_REQUESTS);
            self::clearCacheByPattern(self::CACHE_PREFIX_DEPARTMENT_REQUESTS);
            self::clearCacheByPattern(self::CACHE_PREFIX_APPROVAL_STATS);
            self::clearCacheByPattern(self::CACHE_PREFIX_USER_PERMISSIONS);
            
            // Clear PFMO dashboard cache
            Cache::forget(self::CACHE_PREFIX_PFMO_DASHBOARD);
            
            Log::info('ApprovalCacheService: All approval caches cleared successfully');
            
        } catch (\Exception $e) {
            Log::error('ApprovalCacheService: Error clearing approval caches', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear user-specific approval caches
     * 
     * @param int $userId
     * @return void
     */
    public static function clearUserApprovalCaches(int $userId): void
    {
        try {
            Cache::forget(self::CACHE_PREFIX_USER_REQUESTS . $userId);
            Cache::forget(self::CACHE_PREFIX_USER_PERMISSIONS . $userId);
            
            Log::info('ApprovalCacheService: User approval caches cleared', [
                'user_id' => $userId
            ]);
            
        } catch (\Exception $e) {
            Log::error('ApprovalCacheService: Error clearing user approval caches', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear department-specific approval caches
     * 
     * @param int $departmentId
     * @return void
     */
    public static function clearDepartmentApprovalCaches(int $departmentId): void
    {
        try {
            Cache::forget(self::CACHE_PREFIX_DEPARTMENT_REQUESTS . $departmentId);
            Cache::forget(self::CACHE_PREFIX_APPROVAL_STATS . $departmentId);
            
            Log::info('ApprovalCacheService: Department approval caches cleared', [
                'department_id' => $departmentId
            ]);
            
        } catch (\Exception $e) {
            Log::error('ApprovalCacheService: Error clearing department approval caches', [
                'department_id' => $departmentId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear PFMO-specific caches
     * 
     * @return void
     */
    public static function clearPFMOCaches(): void
    {
        try {
            Cache::forget(self::CACHE_PREFIX_PFMO_DASHBOARD);
            
            // Clear PFMO department caches if we know the department ID
            $pfmoDepartment = \App\Models\Department::where('dept_code', 'PFMO')->first();
            if ($pfmoDepartment) {
                self::clearDepartmentApprovalCaches($pfmoDepartment->department_id);
            }
            
            Log::info('ApprovalCacheService: PFMO caches cleared successfully');
            
        } catch (\Exception $e) {
            Log::error('ApprovalCacheService: Error clearing PFMO caches', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get or cache user approval requests
     * 
     * @param int $userId
     * @param callable $callback
     * @param int $duration
     * @return mixed
     */
    public static function getUserApprovalRequests(int $userId, callable $callback, int $duration = self::DEFAULT_CACHE_DURATION)
    {
        $cacheKey = self::CACHE_PREFIX_USER_REQUESTS . $userId;
        
        return Cache::remember($cacheKey, $duration * 60, $callback);
    }

    /**
     * Get or cache department approval requests
     * 
     * @param int $departmentId
     * @param callable $callback
     * @param int $duration
     * @return mixed
     */
    public static function getDepartmentApprovalRequests(int $departmentId, callable $callback, int $duration = self::DEFAULT_CACHE_DURATION)
    {
        $cacheKey = self::CACHE_PREFIX_DEPARTMENT_REQUESTS . $departmentId;
        
        return Cache::remember($cacheKey, $duration * 60, $callback);
    }

    /**
     * Get or cache approval statistics
     * 
     * @param int $departmentId
     * @param callable $callback
     * @param int $duration
     * @return mixed
     */
    public static function getApprovalStats(int $departmentId, callable $callback, int $duration = self::DEFAULT_CACHE_DURATION)
    {
        $cacheKey = self::CACHE_PREFIX_APPROVAL_STATS . $departmentId;
        
        return Cache::remember($cacheKey, $duration * 60, $callback);
    }

    /**
     * Get or cache PFMO dashboard data
     * 
     * @param callable $callback
     * @param int $duration
     * @return mixed
     */
    public static function getPFMODashboard(callable $callback, int $duration = 15)
    {
        return Cache::remember(self::CACHE_PREFIX_PFMO_DASHBOARD, $duration * 60, $callback);
    }

    /**
     * Get or cache user permissions
     * 
     * @param int $userId
     * @param callable $callback
     * @param int $duration
     * @return mixed
     */
    public static function getUserPermissions(int $userId, callable $callback, int $duration = 60)
    {
        $cacheKey = self::CACHE_PREFIX_USER_PERMISSIONS . $userId;
        
        return Cache::remember($cacheKey, $duration * 60, $callback);
    }

    /**
     * Clear cache by pattern (for cache drivers that support it)
     * 
     * @param string $pattern
     * @return void
     */
    private static function clearCacheByPattern(string $pattern): void
    {
        // For file cache, we can use a simple approach
        // For Redis or other advanced drivers, you might use more sophisticated pattern matching
        
        try {
            $cacheStore = Cache::getStore();
            
            if (method_exists($cacheStore, 'getDirectory')) {
                // File cache - scan directory and remove matching files
                $directory = $cacheStore->getDirectory();
                $files = glob($directory . '/*');
                
                foreach ($files as $file) {
                    $filename = basename($file);
                    if (strpos($filename, md5($pattern)) !== false) {
                        unlink($file);
                    }
                }
            } else {
                // For other cache drivers, we'll need to track keys or use tags
                // This is a simplified implementation - in production you might want to use cache tags
                Log::warning('ApprovalCacheService: Pattern-based cache clearing not fully supported for this cache driver');
            }
            
        } catch (\Exception $e) {
            Log::error('ApprovalCacheService: Error clearing cache by pattern', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Flush all caches (use with caution)
     * 
     * @return void
     */
    public static function flushAllCaches(): void
    {
        try {
            Cache::flush();
            Log::warning('ApprovalCacheService: All caches flushed');
            
        } catch (\Exception $e) {
            Log::error('ApprovalCacheService: Error flushing all caches', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get pending approval count for the current user
     * 
     * @return int
     */
    public static function getPendingApprovalCount(): int
    {
        try {
            // Get current authenticated user
            $user = auth()->user();
            
            if (!$user) {
                return 0;
            }

            $cacheKey = self::CACHE_PREFIX_USER_REQUESTS . $user->Emp_No . '_pending_count';
            
            return Cache::remember($cacheKey, self::DEFAULT_CACHE_DURATION, function() use ($user) {
                // Count pending approvals for this user
                                // Using 'action' column with correct enum values from database
                return \App\Models\FormApproval::where('approver_id', $user->accnt_id)
                    ->whereIn('action', ['Submitted', 'Evaluate', 'Assigned'])
                    ->count();
            });
            
        } catch (\Exception $e) {
            Log::error('ApprovalCacheService: Error getting pending approval count', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}
