@props(['type' => 'success', 'message'])

@php
$bgColor = match($type) {
    'success' => 'bg-gradient-to-r from-green-500 to-emerald-600',
    'error' => 'bg-gradient-to-r from-red-500 to-pink-600',
    'info' => 'bg-gradient-to-r from-blue-500 to-indigo-600',
    'warning' => 'bg-gradient-to-r from-yellow-500 to-orange-500',
    default => 'bg-gradient-to-r from-gray-500 to-slate-600'
};

$borderColor = match($type) {
    'success' => 'border-l-green-400',
    'error' => 'border-l-red-400',
    'info' => 'border-l-blue-400',
    'warning' => 'border-l-yellow-400',
    default => 'border-l-gray-400'
};

$icon = match($type) {
    'success' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    'error' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    'info' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    'warning' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
    default => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
};

$title = match($type) {
    'success' => 'Success!',
    'error' => 'Error!',
    'info' => 'Information',
    'warning' => 'Warning!',
    default => 'Notification'
};
@endphp

<div x-data="{ 
        show: true, 
        progress: 100,
        timer: null,
        startTimer() {
            this.timer = setInterval(() => {
                this.progress -= 2;
                if (this.progress <= 0) {
                    this.show = false;
                    clearInterval(this.timer);
                }
            }, 100);
        },
        pauseTimer() {
            clearInterval(this.timer);
        },
        resumeTimer() {
            this.startTimer();
        }
    }"
     x-show="show"
     x-init="startTimer()"
     @mouseenter="pauseTimer()"
     @mouseleave="resumeTimer()"
     x-transition:enter="transform ease-out duration-500 transition"
     x-transition:enter-start="translate-x-full opacity-0 scale-95"
     x-transition:enter-end="translate-x-0 opacity-100 scale-100"
     x-transition:leave="transform ease-in duration-300 transition"
     x-transition:leave-start="translate-x-0 opacity-100 scale-100"
     x-transition:leave-end="translate-x-full opacity-0 scale-95"
     class="fixed top-4 right-4 z-50 max-w-sm w-full {{ $bgColor }} text-white rounded-xl shadow-2xl border-l-4 {{ $borderColor }} backdrop-blur-sm overflow-hidden group hover:scale-105 transition-all duration-300"
     role="alert">
    
    <!-- Progress Bar -->
    <div class="absolute top-0 left-0 h-1 bg-white/30 transition-all duration-100 ease-linear" 
         :style="`width: ${progress}%`"></div>
    
    <!-- Main Content -->
    <div class="p-4">
        <div class="flex items-start">
            <!-- Icon Container -->
            <div class="flex-shrink-0">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm group-hover:bg-white/30 transition-colors duration-300">
                    {!! $icon !!}
                </div>
            </div>
            
            <!-- Content -->
            <div class="ml-3 flex-1">
                <div class="text-sm font-semibold">{{ $title }}</div>
                <div class="text-sm opacity-90 mt-1">{{ $message }}</div>
            </div>
            
            <!-- Close Button -->
            <button type="button" 
                    @click="show = false; pauseTimer()"
                    class="ml-3 flex-shrink-0 rounded-lg p-1.5 inline-flex h-8 w-8 items-center justify-center hover:bg-white/20 focus:bg-white/20 transition-colors duration-200 group/close"
                    aria-label="Close">
                <svg class="w-4 h-4 group-hover/close:scale-110 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Subtle Animation Effect -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent -skew-x-12 transform -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
</div>

<style>
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-2px); }
    75% { transform: translateX(2px); }
}

.toast-notification.error {
    animation: slideInRight 0.5s ease-out, shake 0.5s ease-in-out 0.5s;
}
</style> 
    'success' => 'bg-green-500',
    'error' => 'bg-red-500',
    'info' => 'bg-blue-500',
    'warning' => 'bg-yellow-500',
    default => 'bg-gray-500'
};

$icon = match($type) {
    'success' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
    'error' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
    'info' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    'warning' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
    default => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
};
@endphp

<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 5000)"
     x-transition:enter="transform ease-out duration-300 transition"
     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed top-4 right-4 flex items-center p-4 mb-4 z-50 rounded-lg shadow {{ $bgColor }} text-white"
     role="alert">
    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg bg-white/25">
        {!! $icon !!}
    </div>
    <div class="ml-3 text-sm font-normal">{{ $message }}</div>
    <button type="button" 
            class="ml-3 -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex h-8 w-8 hover:bg-white/25" 
            aria-label="Close"
            @click="show = false">
        <span class="sr-only">Close</span>
        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
        </svg>
    </button>
</div> 
