@props([
    'title' => 'Alert',
    'type' => 'info', // info, error, warning, success
    'confirmText' => 'OK',
    'cancelText' => 'Cancel',
    'showCancel' => false,
    'id' => 'modal-alert-' . uniqid()
])

@php
$typeClasses = [
    'info' => 'text-blue-600',
    'error' => 'text-red-600',
    'warning' => 'text-yellow-600',
    'success' => 'text-green-600',
];

$bgClasses = [
    'info' => 'bg-gradient-to-br from-blue-50 to-indigo-100',
    'error' => 'bg-gradient-to-br from-red-50 to-pink-100',
    'warning' => 'bg-gradient-to-br from-yellow-50 to-orange-100',
    'success' => 'bg-gradient-to-br from-green-50 to-emerald-100',
];

$buttonClasses = [
    'info' => 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:ring-blue-500',
    'error' => 'bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 focus:ring-red-500',
    'warning' => 'bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-700 hover:to-orange-700 focus:ring-yellow-500',
    'success' => 'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:ring-green-500',
$buttonClasses = [
    'info' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
    'error' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
    'warning' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
    'success' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
];

$icons = [
    'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    'error' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
    'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
    'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
];
@endphp

<div id="{{ $id }}" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay with backdrop blur -->
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity duration-300" onclick="closeModalAlert('{{ $id }}')"></div>

        <!-- Modal panel with enhanced design -->
        <div class="inline-block align-bottom bg-white rounded-2xl px-6 pt-6 pb-6 text-left overflow-hidden shadow-2xl transform transition-all duration-300 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 modal-scale">
            <!-- Decorative top border -->
            <div class="absolute top-0 left-0 right-0 h-1 {{ str_replace('text-', 'bg-', $typeClasses[$type]) }} rounded-t-2xl"></div>
            
            <div class="sm:flex sm:items-start">
                <!-- Enhanced icon container -->
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full {{ $bgClasses[$type] }} sm:mx-0 sm:h-14 sm:w-14 shadow-lg border-2 border-white relative overflow-hidden group">
                    <!-- Animated background -->
                    <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <svg class="h-8 w-8 {{ $typeClasses[$type] }} relative z-10 transform group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$type] }}"></path>
                    </svg>
                </div>
                
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                    <h3 class="text-xl leading-6 font-bold text-gray-900 mb-2" id="modal-title">
                        {{ $title }}
                    </h3>
                    <div class="mt-2">
                        <div class="text-sm text-gray-600 leading-relaxed">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModalAlert('{{ $id }}')"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-{{ $type === 'error' ? 'red' : ($type === 'warning' ? 'yellow' : ($type === 'success' ? 'green' : 'blue')) }}-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 {{ $typeClasses[$type] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$type] }}"></path>
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        {{ $title }}
                    </h3>
                    <div class="mt-2">
                        <div class="text-sm text-gray-500">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced button section -->
            <div class="mt-6 sm:mt-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        onclick="closeModalAlert('{{ $id }}')" 
                        class="w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-lg px-6 py-3 {{ $buttonClasses[$type] }} text-base font-semibold text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm transform hover:scale-105 transition-all duration-200 relative overflow-hidden group">
                    <span class="relative z-10">{{ $confirmText }}</span>
                    <!-- Button animation overlay -->
                    <div class="absolute inset-0 bg-white/20 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
                </button>
                @if($showCancel)
                    <button type="button" 
                            onclick="closeModalAlert('{{ $id }}')" 
                            class="mt-3 w-full inline-flex justify-center items-center rounded-xl border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transform hover:scale-105 transition-all duration-200">
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeModalAlert('{{ $id }}')" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 {{ $buttonClasses[$type] }} text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ $confirmText }}
                </button>
                @if($showCancel)
                    <button type="button" onclick="closeModalAlert('{{ $id }}')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        {{ $cancelText }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.modal-scale {
    transform: scale(0.95);
    transition: transform 0.3s ease-out;
}

.modal-scale.show {
    transform: scale(1);
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes modalSlideOut {
    from {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
    to {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
}
</style>

<script>
function showModalAlert(id) {
    const modal = document.getElementById(id);
    const modalContent = modal.querySelector('.modal-scale');
    
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Enhanced animation
    setTimeout(() => {
        modalContent.classList.add('show');
        modalContent.style.animation = 'modalSlideIn 0.4s ease-out forwards';
    }, 10);
}

function closeModalAlert(id) {
    const modal = document.getElementById(id);
    const modalContent = modal.querySelector('.modal-scale');
    
    modalContent.style.animation = 'modalSlideOut 0.3s ease-in forwards';
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        modalContent.classList.remove('show');
    }, 300);
}

// Enhanced alert function to replace browser alert with improved animations
window.styledAlert = function(message, title = 'Alert', type = 'info') {
    const id = 'dynamic-alert-' + Date.now();
    
    const typeConfig = {
        info: { icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', color: 'blue' },
        error: { icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', color: 'red' },
        warning: { icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z', color: 'yellow' },
        success: { icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', color: 'green' }
    };
    
    const config = typeConfig[type] || typeConfig.info;
    
    const alertHtml = `
        <div id="${id}" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity duration-300" onclick="closeModalAlert('${id}')"></div>
                <div class="inline-block align-bottom bg-white rounded-2xl px-6 pt-6 pb-6 text-left overflow-hidden shadow-2xl transform transition-all duration-300 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 modal-scale">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-${config.color}-600 rounded-t-2xl"></div>
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full bg-gradient-to-br from-${config.color}-50 to-${config.color}-100 sm:mx-0 sm:h-14 sm:w-14 shadow-lg border-2 border-white relative overflow-hidden group">
                            <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <svg class="h-8 w-8 text-${config.color}-600 relative z-10 transform group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${config.icon}"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-xl leading-6 font-bold text-gray-900 mb-2">${title}</h3>
                            <div class="mt-2">
                                <div class="text-sm text-gray-600 leading-relaxed">${message}</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 sm:mt-6 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="closeModalAlert('${id}')" class="w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-lg px-6 py-3 bg-gradient-to-r from-${config.color}-600 to-${config.color}-600 hover:from-${config.color}-700 hover:to-${config.color}-700 text-base font-semibold text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-${config.color}-500 sm:ml-3 sm:w-auto sm:text-sm transform hover:scale-105 transition-all duration-200 relative overflow-hidden group">
                            <span class="relative z-10">OK</span>
                            <div class="absolute inset-0 bg-white/20 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
<script>
function showModalAlert(id) {
    document.getElementById(id).classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeModalAlert(id) {
    document.getElementById(id).classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Enhanced alert function to replace browser alert
window.styledAlert = function(message, title = 'Alert', type = 'info') {
    const id = 'dynamic-alert-' + Date.now();
    const alertHtml = `
        <div id="${id}" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModalAlert('${id}')"></div>
                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">${title}</h3>
                            <div class="mt-2">
                                <div class="text-sm text-gray-500">${message}</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="closeModalAlert('${id}')" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 hover:bg-blue-700 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    showModalAlert(id);
    
    // Auto-remove after closing
    setTimeout(() => {
        const element = document.getElementById(id);
        if (element) element.remove();
    }, 2000);
    
    // Add sound effect for error types (optional)
    if (type === 'error' && 'Audio' in window) {
        try {
            // You can add a subtle sound effect here if desired
            // const audio = new Audio('/sounds/notification.mp3');
            // audio.volume = 0.3;
            // audio.play().catch(() => {}); // Fail silently if no sound
        } catch (e) {
            // Ignore sound errors
        }
    }
};
</script>
</script>
    }, 1000);
};
</script>
