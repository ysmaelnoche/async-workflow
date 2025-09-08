<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body {
                background-image: url('{{ asset('images/loginbackground.png') }}');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                background-attachment: fixed;
            }
            .login-overlay {
                background: rgba(0, 0, 0, 0.4);
                backdrop-filter: blur(2px);
            }
            #userPreview {
                animation: slideDown 0.3s ease-out;
            }
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .avatar-loading {
                background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                background-size: 200% 100%;
                animation: loading 1.5s infinite;
            }
            @keyframes loading {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-900 min-h-screen">
        <!-- Background Overlay -->
        <div class="login-overlay min-h-screen flex flex-col items-center justify-center px-4">
            
            <!-- Logo and Title Section -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center space-x-4">
                    <img src="{{ asset('images/lyceum-logo.png') }}" alt="Lyceum Logo" class="w-12 h-12">
                    <h1 class="text-3xl font-bold text-white">LYCEUM OF ALABANG</h1>
                </div>
            </div>

            <!-- Login Card -->
            <div class="w-full max-w-md">
                <div class="bg-white rounded-xl shadow-2xl p-8">
                    <!-- Workflow Process Logo -->
                    <div class="text-center mb-6">
                        <img src="{{ asset('images/workflowprocess.png') }}" alt="Workflow Process Management System" class="h-16 mx-auto mb-3 object-contain">
                    </div>
                    
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Sign in to your account</h2>
                        <p class="text-gray-600">Please enter your credentials.</p>
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf

                        <!-- Username Field -->
                        <div class="relative">
                            <input id="username" 
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:ring-opacity-50 placeholder-gray-500 transition duration-200" 
                                type="text" 
                                name="username" 
                                value="{{ old('username') }}" 
                                required 
                                autofocus 
                                autocomplete="username" 
                                placeholder="Enter your username" />
                            
                            <!-- User Preview Card -->
                            <div id="userPreview" class="hidden absolute left-0 right-0 top-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-10">
                                <div class="flex items-center space-x-3">
                                    <img id="userAvatar" src="" alt="User Avatar" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
                                    <div class="flex-1">
                                        <div id="userName" class="font-semibold text-gray-900"></div>
                                        <div id="userPosition" class="text-sm text-gray-600"></div>
                                        <div id="userDepartment" class="text-xs text-gray-500"></div>
                                    </div>
                                    <div class="text-green-500">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Loading indicator -->
                            <div id="userLookupLoading" class="hidden absolute right-3 top-1/2 transform -translate-y-1/2">
                                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
                            </div>
                            
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>

                        <!-- Password Field -->
                        <div class="relative">
                            <input id="password" 
                                class="w-full px-4 py-3 pr-12 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:ring-opacity-50 placeholder-gray-500 transition duration-200" 
                                type="password" 
                                name="password" 
                                required 
                                autocomplete="current-password" 
                                placeholder="Enter your password" />
                            
                            <!-- Show/Hide Password Button -->
                            <button type="button" 
                                id="togglePassword"
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700 focus:outline-none">
                                <!-- Eye Icon (Hidden) -->
                                <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <!-- Eye Slash Icon (Visible) -->
                                <svg id="eyeSlashIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                </svg>
                            </button>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between">
                            <label for="remember_me" class="inline-flex items-center">
                                <input id="remember_me" 
                                    type="checkbox" 
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" 
                                    name="remember">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm text-blue-600 hover:text-blue-800 hover:underline rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" 
                                   href="{{ route('password.request') }}">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <!-- Login Button -->
                        <div>
                            <button type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Log in to your account
                            </button>
                        </div>
                    </form>

                    <!-- Additional Options -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Don't have an account? 
                            <a href="#" class="text-blue-600 hover:text-blue-800 hover:underline font-medium">Contact your administrator</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-300">
                    &copy; {{ date('Y') }} Lyceum of Alabang. All rights reserved.
                </p>
            </div>
        </div>

        <!-- Show/Hide Password JavaScript -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const togglePassword = document.getElementById('togglePassword');
                const passwordField = document.getElementById('password');
                const eyeIcon = document.getElementById('eyeIcon');
                const eyeSlashIcon = document.getElementById('eyeSlashIcon');

                togglePassword.addEventListener('click', function() {
                    // Toggle password field type
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    
                    // Toggle eye icons
                    eyeIcon.classList.toggle('hidden');
                    eyeSlashIcon.classList.toggle('hidden');
                });

                // User lookup functionality
                const usernameField = document.getElementById('username');
                const userPreview = document.getElementById('userPreview');
                const userLookupLoading = document.getElementById('userLookupLoading');
                const userAvatar = document.getElementById('userAvatar');
                const userName = document.getElementById('userName');
                const userPosition = document.getElementById('userPosition');
                const userDepartment = document.getElementById('userDepartment');
                
                let lookupTimeout;
                let lastLookupValue = '';

                usernameField.addEventListener('input', function() {
                    const username = this.value.trim();
                    
                    // Clear previous timeout
                    if (lookupTimeout) {
                        clearTimeout(lookupTimeout);
                    }
                    
                    // Hide preview if input is too short or same as last lookup
                    if (username.length < 2) {
                        userPreview.classList.add('hidden');
                        lastLookupValue = '';
                        return;
                    }
                    
                    // Skip if same as last lookup
                    if (username === lastLookupValue) {
                        return;
                    }
                    
                    // Show loading indicator
                    userLookupLoading.classList.remove('hidden');
                    userPreview.classList.add('hidden');
                    
                    // Debounce the lookup request
                    lookupTimeout = setTimeout(function() {
                        lastLookupValue = username;
                        
                        fetch(`/api/user/lookup?username=${encodeURIComponent(username)}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                userLookupLoading.classList.add('hidden');
                                
                                if (data.found && data.user) {
                                    // Populate user preview
                                    userAvatar.src = data.user.avatar_url;
                                    userAvatar.onerror = function() {
                                        this.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(data.user.full_name || data.user.username)}&size=80&background=3b82f6&color=ffffff&bold=true&rounded=true`;
                                    };
                                    
                                    userName.textContent = data.user.full_name || data.user.username;
                                    userPosition.textContent = data.user.position || 'Staff';
                                    userDepartment.textContent = data.user.department || 'Unknown Department';
                                    
                                    // Show preview with animation
                                    userPreview.classList.remove('hidden');
                                    
                                    // Add subtle success indication to input field
                                    usernameField.classList.add('border-green-500');
                                    usernameField.classList.remove('border-red-500');
                                } else {
                                    userPreview.classList.add('hidden');
                                    // Add subtle error indication to input field
                                    usernameField.classList.add('border-red-500');
                                    usernameField.classList.remove('border-green-500');
                                }
                            })
                            .catch(error => {
                                console.error('User lookup error:', error);
                                userLookupLoading.classList.add('hidden');
                                userPreview.classList.add('hidden');
                                usernameField.classList.add('border-red-500');
                                usernameField.classList.remove('border-green-500');
                            });
                    }, 600); // 600ms delay for better UX
                });

                // Reset border colors on focus
                usernameField.addEventListener('focus', function() {
                    this.classList.remove('border-green-500', 'border-red-500');
                    
                    // Show preview again if there's valid data
                    if (this.value.trim().length >= 2 && userName.textContent) {
                        userPreview.classList.remove('hidden');
                    }
                });

                // Hide preview when clicking outside
                document.addEventListener('click', function(event) {
                    if (!usernameField.contains(event.target) && !userPreview.contains(event.target)) {
                        userPreview.classList.add('hidden');
                    }
                });

                // Handle form submission - ensure username field is properly formatted
                document.querySelector('form').addEventListener('submit', function(e) {
                    const username = usernameField.value.trim();
                    if (username !== usernameField.value) {
                        usernameField.value = username;
                    }
                });
            });
        </script>
    </body>
</html>