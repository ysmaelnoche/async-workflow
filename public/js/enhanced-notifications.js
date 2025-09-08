/**
 * Enhanced Notification System
 * Interactive and appealing notifications with modern design
 */

class EnhancedNotificationSystem {
    constructor() {
        this.notifications = [];
        this.maxNotifications = 5;
        this.defaultDuration = 5000;
        this.initializeStyles();
    }

    initializeStyles() {
        if (document.querySelector('#enhanced-notification-styles')) return;
        
        // Ensure document.head exists
        if (!document.head) {
            console.warn('Enhanced Notifications: document.head not available yet');
            return;
        }

        const styles = document.createElement('style');
        styles.id = 'enhanced-notification-styles';
        styles.textContent = `
            .notification-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                display: flex;
                flex-direction: column;
                gap: 12px;
                pointer-events: none;
            }

            .enhanced-notification {
                pointer-events: auto;
                min-width: 320px;
                max-width: 480px;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border-radius: 16px;
                box-shadow: 
                    0 20px 25px -5px rgba(0, 0, 0, 0.1),
                    0 10px 10px -5px rgba(0, 0, 0, 0.04),
                    0 0 0 1px rgba(255, 255, 255, 0.2);
                border: 1px solid rgba(255, 255, 255, 0.3);
                overflow: hidden;
                transform: translateX(100%) scale(0.95);
                opacity: 0;
                transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            }

            .enhanced-notification.show {
                transform: translateX(0) scale(1);
                opacity: 1;
            }

            .enhanced-notification.hide {
                transform: translateX(100%) scale(0.95);
                opacity: 0;
            }

            .notification-header {
                padding: 16px 16px 0 16px;
                display: flex;
                align-items: flex-start;
                gap: 12px;
            }

            .notification-icon {
                flex-shrink: 0;
                width: 40px;
                height: 40px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                overflow: hidden;
            }

            .notification-icon::before {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
                transform: translateX(-100%);
                transition: transform 0.6s ease;
            }

            .notification-icon:hover::before {
                transform: translateX(100%);
            }

            .notification-content {
                flex: 1;
                min-width: 0;
            }

            .notification-title {
                font-size: 14px;
                font-weight: 600;
                color: #1f2937;
                margin: 0 0 4px 0;
                line-height: 1.4;
            }

            .notification-message {
                font-size: 13px;
                color: #6b7280;
                line-height: 1.5;
                margin: 0;
            }

            .notification-actions {
                padding: 0 16px 16px 16px;
                display: flex;
                gap: 8px;
                justify-content: flex-end;
                margin-top: 12px;
            }

            .notification-button {
                padding: 6px 12px;
                border-radius: 8px;
                border: none;
                font-size: 12px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
                position: relative;
                overflow: hidden;
            }

            .notification-button:hover {
                transform: translateY(-1px);
            }

            .notification-button.primary {
                background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                color: white;
            }

            .notification-button.secondary {
                background: rgba(107, 114, 128, 0.1);
                color: #374151;
                border: 1px solid rgba(107, 114, 128, 0.2);
            }

            .notification-close {
                position: absolute;
                top: 8px;
                right: 8px;
                width: 24px;
                height: 24px;
                border: none;
                background: rgba(107, 114, 128, 0.1);
                color: #6b7280;
                border-radius: 6px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
                opacity: 0;
            }

            .enhanced-notification:hover .notification-close {
                opacity: 1;
            }

            .notification-close:hover {
                background: rgba(239, 68, 68, 0.1);
                color: #ef4444;
                transform: scale(1.1);
            }

            .notification-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background: linear-gradient(90deg, #3b82f6, #1d4ed8);
                transition: width 0.1s linear;
                border-radius: 0 0 16px 16px;
            }

            /* Type-specific styles */
            .notification-success .notification-icon {
                background: linear-gradient(135deg, #10b981, #059669);
                color: white;
            }

            .notification-error .notification-icon {
                background: linear-gradient(135deg, #ef4444, #dc2626);
                color: white;
            }

            .notification-warning .notification-icon {
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: white;
            }

            .notification-info .notification-icon {
                background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                color: white;
            }

            @keyframes notificationSlideIn {
                from {
                    transform: translateX(100%) scale(0.9);
                    opacity: 0;
                }
                to {
                    transform: translateX(0) scale(1);
                    opacity: 1;
                }
            }

            @keyframes notificationShake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-4px); }
                75% { transform: translateX(4px); }
            }

            @keyframes notificationBounce {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }

            .notification-shake {
                animation: notificationShake 0.5s ease-in-out;
            }

            .notification-bounce {
                animation: notificationBounce 0.3s ease-in-out;
            }

            @media (max-width: 480px) {
                .notification-container {
                    left: 12px;
                    right: 12px;
                    top: 12px;
                }

                .enhanced-notification {
                    min-width: unset;
                    max-width: unset;
                }
            }
        `;

        document.head.appendChild(styles);
        this.createContainer();
    }

    createContainer() {
        if (document.querySelector('.notification-container')) return;
        
        // Ensure document.body exists
        if (!document.body) {
            console.warn('Enhanced Notifications: document.body not available yet');
            return;
        }

        const container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
    }

    getIcon(type) {
        const icons = {
            success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
        };
        return icons[type] || icons.info;
    }

    show(options = {}) {
        const {
            type = 'info',
            title = 'Notification',
            message = '',
            duration = this.defaultDuration,
            actions = [],
            persistent = false,
            onClose = null,
            onClick = null
        } = options;

        // Limit maximum notifications
        if (this.notifications.length >= this.maxNotifications) {
            this.notifications[0].remove();
        }

        const id = 'notification-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        const container = document.querySelector('.notification-container');

        const notification = document.createElement('div');
        notification.id = id;
        notification.className = `enhanced-notification notification-${type}`;

        // Progress bar for timed notifications
        const progressBar = !persistent && duration > 0 ? 
            `<div class="notification-progress" style="width: 100%"></div>` : '';

        // Actions buttons
        const actionButtons = actions.length > 0 ? 
            `<div class="notification-actions">
                ${actions.map((action, index) => 
                    `<button class="notification-button ${action.primary ? 'primary' : 'secondary'}" 
                             data-action="${index}">
                        ${action.text}
                     </button>`
                ).join('')}
             </div>` : '';

        notification.innerHTML = `
            <button class="notification-close" aria-label="Close">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <div class="notification-header">
                <div class="notification-icon">
                    ${this.getIcon(type)}
                </div>
                <div class="notification-content">
                    <h4 class="notification-title">${title}</h4>
                    <p class="notification-message">${message}</p>
                </div>
            </div>
            ${actionButtons}
            ${progressBar}
        `;

        // Add event listeners
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => this.remove(id));

        // Action button handlers
        if (actions.length > 0) {
            const actionBtns = notification.querySelectorAll('[data-action]');
            actionBtns.forEach((btn, index) => {
                btn.addEventListener('click', () => {
                    if (actions[index].handler) {
                        actions[index].handler();
                    }
                    if (actions[index].closeOnClick !== false) {
                        this.remove(id);
                    }
                });
            });
        }

        // Click handler for entire notification
        if (onClick) {
            notification.style.cursor = 'pointer';
            notification.addEventListener('click', (e) => {
                if (!e.target.closest('.notification-close') && !e.target.closest('.notification-actions')) {
                    onClick();
                }
            });
        }

        // Add to DOM and show
        container.appendChild(notification);
        
        // Trigger entrance animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Store notification data
        const notificationData = {
            id,
            element: notification,
            timer: null,
            onClose
        };

        this.notifications.push(notificationData);

        // Auto-remove after duration (if not persistent)
        if (!persistent && duration > 0) {
            this.startTimer(notificationData, duration);
        }

        // Add hover pause functionality
        notification.addEventListener('mouseenter', () => {
            if (notificationData.timer) {
                this.pauseTimer(notificationData);
            }
        });

        notification.addEventListener('mouseleave', () => {
            if (!persistent && duration > 0) {
                this.resumeTimer(notificationData, duration);
            }
        });

        return id;
    }

    startTimer(notificationData, duration) {
        const progressBar = notificationData.element.querySelector('.notification-progress');
        let elapsed = 0;
        const interval = 50;

        notificationData.timer = setInterval(() => {
            elapsed += interval;
            const progress = 100 - (elapsed / duration * 100);
            
            if (progressBar) {
                progressBar.style.width = progress + '%';
            }

            if (elapsed >= duration) {
                this.remove(notificationData.id);
            }
        }, interval);
    }

    pauseTimer(notificationData) {
        if (notificationData.timer) {
            clearInterval(notificationData.timer);
            notificationData.timer = null;
        }
    }

    resumeTimer(notificationData, duration) {
        if (!notificationData.timer) {
            const progressBar = notificationData.element.querySelector('.notification-progress');
            const currentWidth = progressBar ? parseFloat(progressBar.style.width) : 100;
            const remainingTime = (currentWidth / 100) * duration;
            
            this.startTimer(notificationData, remainingTime);
        }
    }

    remove(id) {
        const index = this.notifications.findIndex(n => n.id === id);
        if (index === -1) return;

        const notificationData = this.notifications[index];
        const notification = notificationData.element;

        // Clear timer
        if (notificationData.timer) {
            clearInterval(notificationData.timer);
        }

        // Trigger exit animation
        notification.classList.add('hide');

        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
            
            // Call onClose callback
            if (notificationData.onClose) {
                notificationData.onClose();
            }
            
            // Remove from array
            this.notifications.splice(index, 1);
        }, 500);
    }

    clear() {
        this.notifications.forEach(notification => {
            this.remove(notification.id);
        });
    }

    // Convenience methods
    success(title, message, options = {}) {
        return this.show({
            type: 'success',
            title,
            message,
            ...options
        });
    }

    error(title, message, options = {}) {
        return this.show({
            type: 'error',
            title,
            message,
            duration: 8000, // Longer duration for errors
            ...options
        });
    }

    warning(title, message, options = {}) {
        return this.show({
            type: 'warning',
            title,
            message,
            ...options
        });
    }

    info(title, message, options = {}) {
        return this.show({
            type: 'info',
            title,
            message,
            ...options
        });
    }
}

// Create global instance
// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        window.EnhancedNotifications = new EnhancedNotificationSystem();
    });
} else {
    // DOM is already ready
    window.EnhancedNotifications = new EnhancedNotificationSystem();
}

// Convenience global functions
window.showNotification = (type, title, message, options = {}) => {
    return window.EnhancedNotifications.show({
        type,
        title,
        message,
        ...options
    });
};

window.showSuccessNotification = (title, message, options = {}) => {
    return window.EnhancedNotifications.success(title, message, options);
};

window.showErrorNotification = (title, message, options = {}) => {
    return window.EnhancedNotifications.error(title, message, options);
};

window.showWarningNotification = (title, message, options = {}) => {
    return window.EnhancedNotifications.warning(title, message, options);
};

window.showInfoNotification = (title, message, options = {}) => {
    return window.EnhancedNotifications.info(title, message, options);
};

// Simple Error Toast Function - Small, red, bouncy
window.showSimpleErrorToast = (message) => {
    // Remove any existing simple error toasts
    const existingToasts = document.querySelectorAll('.simple-error-toast');
    existingToasts.forEach(toast => toast.remove());

    // Create the simple error toast
    const toast = document.createElement('div');
    toast.className = 'simple-error-toast fixed top-20 right-4 z-50 max-w-xs bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg border border-red-600';
    toast.style.animation = 'simpleErrorBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
    
    toast.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0 mr-3">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="text-sm font-medium">${message}</div>
            <button type="button" class="ml-3 flex-shrink-0 rounded p-1 hover:bg-red-600 transition-colors duration-200" onclick="this.closest('.simple-error-toast').remove()">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    `;

    // Add animation styles if not present
    if (!document.querySelector('#simple-error-toast-styles')) {
        const styles = document.createElement('style');
        styles.id = 'simple-error-toast-styles';
        styles.textContent = `
            @keyframes simpleErrorBounce {
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
        `;
        document.head.appendChild(styles);
    }

    // Add to page
    document.body.appendChild(toast);

    // Auto remove after 4 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'none';
            toast.style.transform = 'translateX(100%) scale(0.8)';
            toast.style.opacity = '0';
            toast.style.transition = 'all 0.3s ease-in';
            setTimeout(() => toast.remove(), 300);
        }
    }, 4000);

    return toast;
};
