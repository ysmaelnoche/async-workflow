<?php

namespace App\Services;

use App\Models\Department;
use App\Models\IomDetail;

class RequestTypeService
{
    /**
     * Request type to department mapping rules
     * This configuration defines which request types should automatically go to which departments
     */
    private static array $requestTypeDepartmentMapping = [
        // PFMO Department - Physical Facilities Management Office
        'facility_maintenance' => [
            'department_code' => 'PFMO',
            'keywords' => [
                // HVAC & Aircon
                'aircon', 'air conditioning', 'ac repair', 'aircon repair',
                'hvac', 'ventilation', 'heating', 'cooling',
                // Electrical
                'electrical', 'electrical repair', 'electricity', 'power',
                'lighting', 'lights', 'bulb', 'fluorescent', 'generator', 'ups',
                // Plumbing
                'plumbing', 'water', 'pipe', 'faucet', 'toilet', 'restroom',
                'drainage', 'leak', 'water supply',
                // Construction & Building
                'construction', 'renovation', 'building', 'structural',
                'concrete', 'cement', 'foundation', 'roof', 'ceiling', 'wall',
                'floor', 'paint', 'painting', 'tiles', 'carpentry', 'welding',
                'steel', 'framework',
                // Housekeeping & Cleaning
                'cleaning', 'housekeeping', 'janitorial', 'sanitization',
                'disinfection', 'mopping', 'waste management', 'garbage',
                // General Services
                'equipment', 'furniture', 'delivery', 'procurement',
                'supplies', 'logistics', 'inventory',
                // General Maintenance
                'maintenance', 'repair', 'facility maintenance', 'building maintenance'
            ],
            'specific_request_types' => [
                'Request for Facilities'
            ]
        ],
        
        // Housekeeping Department
        'housekeeping' => [
            'department_code' => 'HSK',
            'keywords' => [
                'cleaning', 'housekeeping', 'janitor', 'janitorial',
                'sanitization', 'disinfection', 'mopping',
                'waste management', 'garbage', 'trash',
                'carpet cleaning', 'floor cleaning',
                'window cleaning', 'general cleaning'
            ]
        ],
        
        // IT Department
        'information_technology' => [
            'department_code' => 'ICT',
            'keywords' => [
                'computer', 'laptop', 'printer', 'network',
                'internet', 'wifi', 'software', 'hardware',
                'system', 'database', 'email', 'it support',
                'technical support', 'computer laboratory',
                'audio visual', 'projector', 'av equipment'
            ],
            'specific_request_types' => [
                'Request for Computer Laboratory'
            ]
        ],
        
        // Security Department
        'security' => [
            'department_code' => 'SEC',
            'keywords' => [
                'security', 'guard', 'cctv', 'surveillance',
                'access control', 'key', 'lock', 'safety',
                'emergency', 'incident', 'patrol'
            ]
        ],
        
        // Administrative Services (for venues, events)
        'administrative_services' => [
            'department_code' => 'ADMIN',
            'keywords' => [
                'venue', 'event', 'meeting room', 'conference room',
                'auditorium', 'hall', 'reservation', 'booking',
                'ceremony', 'graduation', 'seminar'
            ],
            'specific_request_types' => [
                'Request for Venue'
            ]
        ]
    ];

    /**
     * Sub-department mapping for PFMO requests based on actual database
     * When a request is assigned to PFMO, determine which sub-department should handle it
     */
    private static array $pfmoSubDepartmentMapping = [
        'construction' => [
            'sub_department_id' => 1,
            'name' => 'Construction Division',
            'code' => 'PFMO-CONS',
            'keywords' => [
                'construction', 'renovation', 'building', 'concrete', 'cement',
                'structural', 'foundation', 'roof', 'ceiling', 'wall',
                'floor', 'paint', 'painting', 'tiles', 'carpentry',
                'welding', 'steel', 'framework', 'building maintenance',
                'infrastructure', 'demolition', 'installation', 'assembly'
            ]
        ],
        
        'housekeeping' => [
            'sub_department_id' => 2,
            'name' => 'Housekeeping Division',
            'code' => 'PFMO-HOUSE',
            'keywords' => [
                'cleaning', 'housekeeping', 'janitorial', 'sanitization',
                'disinfection', 'mopping', 'sweeping', 'waste management',
                'garbage', 'trash', 'carpet cleaning', 'floor cleaning',
                'window cleaning', 'restroom cleaning', 'hygiene'
            ]
        ],
        
        'general_services' => [
            'sub_department_id' => 3,
            'name' => 'General Services Division',
            'code' => 'PFMO-GEN',
            'keywords' => [
                'equipment', 'supplies', 'furniture', 'delivery',
                'transportation', 'logistics', 'inventory', 'procurement',
                'general services', 'miscellaneous', 'other services',
                'electrical', 'plumbing', 'aircon', 'air conditioning',
                'maintenance', 'repair', 'hvac', 'lights', 'lighting'
            ]
        ]
    ];

    /**
     * AI-Enhanced Auto-Assignment: Intelligently determine the target department 
     * using advanced text analysis and confidence scoring
     * 
     * @param string $title Request title/subject
     * @param string $description Request description/body
     * @param string|null $specificRequestType Specific request type if purpose is "Request"
     * @param string|null $purpose IOM purpose
     * @return array|null Department information or null if no match
     */
    public static function autoAssignDepartment(
        string $title,
        string $description,
        ?string $specificRequestType = null,
        ?string $purpose = null
    ): ?array {
        // Combine all text for AI-like analysis
        $fullText = strtolower(trim($title . ' ' . $description . ' ' . $specificRequestType . ' ' . $purpose));
        
        if (empty($fullText) || strlen($fullText) < 5) {
            return null;
        }

        $departmentScores = [];
        
        // Enhanced scoring system with AI-like features
        foreach (self::$requestTypeDepartmentMapping as $category => $config) {
            $score = 0;
            $matchedKeywords = [];
            
            // Phase 1: Specific Request Type Priority (Highest confidence)
            if (!empty($specificRequestType) && isset($config['specific_request_types'])) {
                foreach ($config['specific_request_types'] as $specificType) {
                    if (stripos($specificRequestType, $specificType) !== false) {
                        $score += 200; // Very high priority for exact matches
                        $matchedKeywords[] = "Exact match: {$specificType}";
                    }
                }
            }
            
            // Phase 2: Enhanced keyword analysis with context
            foreach ($config['keywords'] as $keyword) {
                $keywordLower = strtolower($keyword);
                $keywordCount = substr_count($fullText, $keywordLower);
                
                if ($keywordCount > 0) {
                    // Base score
                    $baseScore = $keywordCount * (strlen($keyword) > 4 ? 15 : 8);
                    
                    // Context bonus: keywords in title get higher score
                    $titleText = strtolower($title);
                    if (strpos($titleText, $keywordLower) !== false) {
                        $baseScore += 10; // Title bonus
                    }
                    
                    // Urgency detection
                    $urgencyWords = ['urgent', 'emergency', 'asap', 'critical', 'broken'];
                    foreach ($urgencyWords as $urgentWord) {
                        if (strpos($fullText, $urgentWord) !== false) {
                            $baseScore += 5; // Urgency bonus
                        }
                    }
                    
                    $score += $baseScore;
                    $matchedKeywords[] = $keyword . " (score: {$baseScore})";
                }
            }
            
            // Phase 3: Intent analysis - detect action words
            $actionWords = ['repair', 'fix', 'broken', 'maintenance', 'service', 'help', 'need', 'problem'];
            foreach ($actionWords as $action) {
                if (strpos($fullText, $action) !== false) {
                    $score += 8; // Intent bonus
                }
            }
            
            if ($score > 0) {
                $confidenceLevel = 'Very Low';
                if ($score >= 100) $confidenceLevel = 'Very High';
                elseif ($score >= 50) $confidenceLevel = 'High';
                elseif ($score >= 25) $confidenceLevel = 'Medium';
                elseif ($score >= 15) $confidenceLevel = 'Low';
                
                $departmentScores[$category] = [
                    'score' => $score,
                    'department_code' => $config['department_code'],
                    'category' => $category,
                    'matched_keywords' => $matchedKeywords,
                    'confidence_level' => $confidenceLevel
                ];
            }
        }
        
        if (empty($departmentScores)) {
            return null;
        }
        
        // Get the highest scoring department
        $topMatch = array_reduce($departmentScores, function($carry, $item) {
            return ($carry === null || $item['score'] > $carry['score']) ? $item : $carry;
        });
        
        // Dynamic confidence threshold based on text length
        $minThreshold = strlen($fullText) > 50 ? 12 : 15;
        
        if ($topMatch['score'] >= $minThreshold) {
            $department = Department::where('dept_code', $topMatch['department_code'])->first();
            
            if ($department) {
                return [
                    'department' => $department,
                    'category' => $topMatch['category'],
                    'confidence_score' => $topMatch['score'],
                    'confidence_level' => $topMatch['confidence_level'],
                    'auto_assigned' => true,
                    'reasoning' => "AI Analysis: " . implode(', ', array_slice($topMatch['matched_keywords'], 0, 3)),
                    'ai_features' => [
                        'context_analysis' => true,
                        'intent_detection' => true,
                        'urgency_detection' => true,
                        'dynamic_threshold' => $minThreshold
                    ]
                ];
            }
        }
        
        return null;
    }

    /**
     * Get PFMO sub-department assignment based on request content
     * 
     * @param string $title Request title
     * @param string $description Request description
     * @return array|null Sub-department info or null if no specific match
     */
    public static function getPFMOSubDepartmentAssignment(string $title, string $description): ?array
    {
        $searchText = strtolower(trim($title . ' ' . $description));
        
        if (empty($searchText)) {
            return null;
        }

        $scores = [];
        
        foreach (self::$pfmoSubDepartmentMapping as $subdept => $config) {
            $score = 0;
            
            foreach ($config['keywords'] as $keyword) {
                $keywordCount = substr_count($searchText, strtolower($keyword));
                if ($keywordCount > 0) {
                    $score += $keywordCount * (strlen($keyword) > 3 ? 10 : 5);
                }
            }
            
            if ($score > 0) {
                $scores[$subdept] = [
                    'score' => $score,
                    'config' => $config
                ];
            }
        }
        
        if (empty($scores)) {
            // Default to general services if no specific match
            return [
                'sub_department' => 'general_services',
                'sub_department_id' => self::$pfmoSubDepartmentMapping['general_services']['sub_department_id'],
                'name' => self::$pfmoSubDepartmentMapping['general_services']['name'],
                'code' => self::$pfmoSubDepartmentMapping['general_services']['code'],
                'confidence_score' => 0,
                'is_default' => true
            ];
        }
        
        // Get highest scoring sub-department
        $topMatch = array_reduce($scores, function($carry, $item) {
            return ($carry === null || $item['score'] > $carry['score']) ? $item : $carry;
        });
        
        $subDeptKey = array_search($topMatch, $scores);
        
        return [
            'sub_department' => $subDeptKey,
            'sub_department_id' => $topMatch['config']['sub_department_id'],
            'name' => $topMatch['config']['name'],
            'code' => $topMatch['config']['code'],
            'confidence_score' => $topMatch['score'],
            'is_default' => false
        ];
    }

    /**
     * Get all available request categories for management interface
     * 
     * @return array
     */
    public static function getRequestCategories(): array
    {
        $categories = [];
        
        foreach (self::$requestTypeDepartmentMapping as $category => $config) {
            $department = Department::where('dept_code', $config['department_code'])->first();
            
            $categories[$category] = [
                'name' => ucwords(str_replace('_', ' ', $category)),
                'department' => $department ? $department->dept_name : 'Unknown',
                'department_code' => $config['department_code'],
                'keywords' => $config['keywords'],
                'specific_types' => $config['specific_request_types'] ?? []
            ];
        }
        
        return $categories;
    }

    /**
     * Get PFMO sub-departments for management
     * 
     * @return array
     */
    public static function getPFMOSubDepartments(): array
    {
        $subDepts = [];
        
        foreach (self::$pfmoSubDepartmentMapping as $key => $config) {
            $subDepts[$key] = [
                'name' => $config['name'],
                'head_email' => $config['head_email'],
                'keywords' => $config['keywords']
            ];
        }
        
        return $subDepts;
    }

    /**
     * Suggest department based on partial input (for UI autocomplete)
     * 
     * @param string $partialText
     * @return array
     */
    public static function suggestDepartments(string $partialText): array
    {
        $suggestions = [];
        $searchText = strtolower(trim($partialText));
        
        if (strlen($searchText) < 3) {
            return $suggestions;
        }

        foreach (self::$requestTypeDepartmentMapping as $category => $config) {
            $score = 0;
            $matchedKeywords = [];
            
            foreach ($config['keywords'] as $keyword) {
                if (stripos($keyword, $searchText) !== false || stripos($searchText, $keyword) !== false) {
                    $score += 10;
                    $matchedKeywords[] = $keyword;
                }
            }
            
            if ($score > 0) {
                $department = Department::where('dept_code', $config['department_code'])->first();
                
                if ($department) {
                    $suggestions[] = [
                        'department' => $department,
                        'category' => ucwords(str_replace('_', ' ', $category)),
                        'matched_keywords' => $matchedKeywords,
                        'confidence' => min($score, 100)
                    ];
                }
            }
        }
        
        // Sort by confidence
        usort($suggestions, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });
        
        return array_slice($suggestions, 0, 5); // Return top 5 suggestions
    }
}
