@props(['message'])

<div x-data="{ 
        show: true,
        timer: null,
        startTimer() {
            this.timer = setTimeout(() => {
                this.show = false;
            }, 4000);
        }
    }"
     x-show="show"
     x-init="startTimer()"
     x-transition:enter="transform transition ease-out duration-300"
     x-transition:enter-start="translate-y-2 opacity-0 scale-95"
     x-transition:enter-end="translate-y-0 opacity-100 scale-100"
     x-transition:leave="transform transition ease-in duration-200"
     x-transition:leave-start="translate-y-0 opacity-100 scale-100"
     x-transition:leave-end="-translate-y-2 opacity-0 scale-95"
     class="fixed top-20 right-4 z-50 max-w-xs bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg border border-red-600 simple-error-bounce"
     role="alert">
    
    <div class="flex items-center">
        <!-- Simple Error Icon -->
        <div class="flex-shrink-0 mr-3">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
        </div>
        
        <!-- Message -->
        <div class="text-sm font-medium">{{ $message }}</div>
        
        <!-- Close Button -->
        <button type="button" 
                @click="show = false; clearTimeout(timer)"
                class="ml-3 flex-shrink-0 rounded p-1 hover:bg-red-600 transition-colors duration-200"
                aria-label="Close">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>
</div>

<style>
@keyframes errorBounce {
    0% {
        transform: translateX(100%) scale(0.8);
        opacity: 0;
    }
    60% {
        transform: translateX(-5px) scale(1.05);
        opacity: 1;
    }
    80% {
        transform: translateX(2px) scale(0.98);
    }
    100% {
        transform: translateX(0) scale(1);
        opacity: 1;
    }
}

.simple-error-bounce {
    animation: errorBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
</style>
