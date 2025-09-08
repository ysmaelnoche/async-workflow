# Dynamic User Detection Feature

## Overview

This feature adds dynamic user detection to the login form. When users type their username, the system will:

-   Show a loading indicator while searching
-   Display the user's avatar, full name, position, and department if found
-   Provide visual feedback (green/red border) based on search results
-   Use fallback avatar generation if no custom avatar exists

## How it Works

### 1. Frontend (JavaScript)

-   Listens for input changes on the username field
-   Debounces requests (600ms delay) to avoid excessive API calls
-   Shows/hides user preview card with animation
-   Handles loading states and error conditions

### 2. Backend (API)

-   **Route**: `GET /api/user/lookup?username={username}`
-   **Controller**: `UserLookupController`
-   Searches for users by exact username match
-   Returns user information including avatar URL
-   Includes relationships: `employeeInfo` and `department`

### 3. Avatar System

-   Primary: Checks for stored avatars in `storage/app/public/avatars/`
-   Fallback: Uses UI Avatars service for initials-based avatars
-   Supports both JPG and PNG formats
-   Generates URLs for both stored and fallback avatars

## File Structure

```
app/
├── Http/Controllers/Api/
│   └── UserLookupController.php    # API controller for user lookup
├── Console/Commands/
│   └── GenerateSampleAvatars.php   # Command to generate sample avatars
└── Models/
    ├── User.php                    # User model with relationships
    ├── EmployeeInfo.php            # Employee information model
    └── Department.php              # Department model

resources/views/
└── welcome.blade.php               # Login form with dynamic detection

routes/
└── web.php                         # API route registration

storage/app/public/
└── avatars/                        # Avatar storage directory
```

## Configuration

### Avatar Storage

-   Avatars are stored in `storage/app/public/avatars/`
-   Naming convention: `{username}.jpg` or `{username}.png`
-   Accessible via: `asset('storage/avatars/{username}.{ext}')`

### Fallback Avatar Service

-   Uses UI Avatars: `https://ui-avatars.com/api/`
-   Parameters: name, size=80, background=3b82f6, color=ffffff, bold=true, rounded=true

## Usage

### For Users

1. Start typing username in the login field
2. Wait for the loading indicator
3. View user information when found
4. Continue with password entry

### For Administrators

1. Upload user avatars to `storage/app/public/avatars/`
2. Use naming convention: `{username}.jpg` or `{username}.png`
3. Run `php artisan storage:link` to create public symlink

### Generate Sample Avatars

```bash
php artisan avatars:generate-samples
```

## API Response Format

### Success Response

```json
{
    "found": true,
    "user": {
        "username": "CCS-2025-0001",
        "employee_number": "EMP001",
        "full_name": "Juan Dela Cruz",
        "position": "Staff",
        "department": "Computer Science",
        "avatar_url": "https://example.com/storage/avatars/CCS-2025-0001.jpg"
    }
}
```

### Not Found Response

```json
{
    "found": false,
    "message": "User not found"
}
```

## Security Considerations

-   API endpoint is accessible without authentication (before login)
-   Only returns basic user information (no sensitive data)
-   Rate limiting can be added if needed
-   Input validation prevents injection attacks

## Browser Compatibility

-   Works on all modern browsers
-   Uses Fetch API (IE11+ support)
-   Graceful degradation for older browsers
-   CSS animations with fallbacks

## Performance Features

-   Debounced API requests (600ms delay)
-   Caches last lookup to avoid duplicate requests
-   Lazy loads avatars with error handling
-   Minimal DOM manipulation for smooth UX
