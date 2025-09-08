// Enhanced Notification badge updater with animations and better UX
document.addEventListener('DOMContentLoaded', function() {
    // Check if the user has approval access (check if the Approvals link exists)
    const approvalsLink = document.querySelector('a[href*="approvals.index"]');
    if (!approvalsLink) return;

    // Log only in debug mode
    if (window.debugMode) {
        console.log('🔔 Enhanced notification badge updater initialized');
    }
    
    // Function to create enhanced notification badge
    function createEnhancedBadge(count) {
        const badge = document.createElement('span');
        badge.className = 'notification-badge enhanced-badge';
        
        // Enhanced styling with animations
        badge.style.cssText = `
            position: absolute;
            top: 15%;
            right: 0;
            transform: translateX(75%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.25rem;
            height: 1.25rem;
            padding: 0 0.25rem;
            font-size: 0.7rem;
            font-weight: 700;
            color: white;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border: 2px solid white;
            border-radius: 50px;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
            animation: pulseNotification 2s infinite;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 10;
        `;
        
        badge.textContent = count > 99 ? '99+' : count;
        return badge;
    }
    
    // Function to update notification badge with enhanced animations
    function updateNotificationBadge() {
        if (window.debugMode) {
            console.log('🔄 Fetching notification count...');
        }
        
        // Add loading state to approvals link
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'notification-loading';
        loadingIndicator.style.cssText = `
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: #3b82f6;
            border-radius: 50%;
            animation: pulse 1s infinite;
            opacity: 0.7;
        `;
        
        approvalsLink.style.position = 'relative';
        approvalsLink.appendChild(loadingIndicator);
        
        fetch('/notifications/count')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                const count = data.count;
                if (window.debugMode) {
                    console.log('✅ Notification count received:', count);
                }
                
                // Remove loading indicator
                if (loadingIndicator.parentNode) {
                    loadingIndicator.remove();
                }
                
                // Remove existing badges
                const existingBadges = approvalsLink.querySelectorAll('.notification-badge');
                existingBadges.forEach(badge => {
                    badge.style.animation = 'fadeOut 0.3s ease-out forwards';
                    setTimeout(() => badge.remove(), 300);
                });
                
                // Add new badge if count > 0
                if (count > 0) {
                    setTimeout(() => {
                        const newBadge = createEnhancedBadge(count);
                        approvalsLink.appendChild(newBadge);
                        
                        // Trigger entrance animation
                        setTimeout(() => {
                            newBadge.style.animation = 'bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55), pulseNotification 2s infinite 0.6s';
                        }, 10);
                        
                        // Add hover effects
                        approvalsLink.addEventListener('mouseenter', function() {
                            newBadge.style.transform = 'translateX(75%) scale(1.1)';
                            newBadge.style.boxShadow = '0 4px 12px rgba(239, 68, 68, 0.6)';
                        });
                        
                        approvalsLink.addEventListener('mouseleave', function() {
                            newBadge.style.transform = 'translateX(75%) scale(1)';
                            newBadge.style.boxShadow = '0 2px 8px rgba(239, 68, 68, 0.4)';
                        });
                        
                        // Show notification toast for new notifications
                        if (window.lastNotificationCount !== undefined && count > window.lastNotificationCount) {
                            showNotificationToast(count - window.lastNotificationCount);
                        }
                        
                        window.lastNotificationCount = count;
                        
                    }, 300);
                } else {
                    window.lastNotificationCount = 0;
                }
            })
            .catch(error => {
                console.error('❌ Error fetching notification count:', error);
                
                // Remove loading indicator on error
                if (loadingIndicator.parentNode) {
                    loadingIndicator.remove();
                }
                
                // Show error indicator briefly
                const errorBadge = document.createElement('span');
                errorBadge.textContent = '!';
                errorBadge.style.cssText = `
                    position: absolute;
                    top: 15%;
                    right: 0;
                    transform: translateX(75%);
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 1.25rem;
                    height: 1.25rem;
                    font-size: 0.7rem;
                    font-weight: 700;
                    color: white;
                    background: #ef4444;
                    border: 2px solid white;
                    border-radius: 50%;
                    animation: shake 0.5s ease-in-out;
                `;
                
                approvalsLink.appendChild(errorBadge);
                setTimeout(() => errorBadge.remove(), 2000);
            });
    }
    
    // Function to show notification toast for new notifications
    function showNotificationToast(newCount) {
        if (document.querySelector('.new-notification-toast')) return; // Prevent duplicates
        
        const toast = document.createElement('div');
        toast.className = 'new-notification-toast';
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 12px 16px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
            z-index: 1000;
            font-size: 14px;
            font-weight: 600;
            animation: slideInRight 0.5s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        `;
        
        toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17h-7a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2z"/>
                </svg>
                <span>${newCount} new notification${newCount !== 1 ? 's' : ''}!</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-in forwards';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
        
        // Click to dismiss
        toast.addEventListener('click', () => {
            toast.style.animation = 'slideOutRight 0.3s ease-in forwards';
            setTimeout(() => toast.remove(), 300);
        });
    }
    
    // Update immediately and then periodically
    updateNotificationBadge();
    
    // Update every 30 seconds with exponential backoff on errors
    let updateInterval = 30000;
    let errorCount = 0;
    
    function scheduleNextUpdate() {
        setTimeout(() => {
            updateNotificationBadge();
            scheduleNextUpdate();
        }, updateInterval);
    }
    
    scheduleNextUpdate();
    
    // Add CSS animations if not already present
    if (!document.querySelector('#notification-animations')) {
        const style = document.createElement('style');
        style.id = 'notification-animations';
        style.textContent = `
            @keyframes pulseNotification {
                0%, 100% { 
                    transform: translateX(75%) scale(1);
                    opacity: 1;
                }
                50% { 
                    transform: translateX(75%) scale(1.05);
                    opacity: 0.9;
                }
            }
            
            @keyframes bounceIn {
                0% {
                    transform: translateX(75%) scale(0.3);
                    opacity: 0;
                }
                50% {
                    transform: translateX(75%) scale(1.1);
                    opacity: 0.8;
                }
                100% {
                    transform: translateX(75%) scale(1);
                    opacity: 1;
                }
            }
            
            @keyframes fadeOut {
                from {
                    transform: translateX(75%) scale(1);
                    opacity: 1;
                }
                to {
                    transform: translateX(75%) scale(0.3);
                    opacity: 0;
                }
            }
            
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
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            @keyframes shake {
                0%, 100% { transform: translateX(75%) translateY(0); }
                25% { transform: translateX(75%) translateY(-2px); }
                75% { transform: translateX(75%) translateY(2px); }
            }
            
            @keyframes pulse {
                0%, 100% { opacity: 0.7; transform: scale(1); }
                50% { opacity: 1; transform: scale(1.1); }
            }
        `;
        document.head.appendChild(style);
    }
});

// Global function to manually trigger notification update (for use in other scripts)
window.refreshNotificationBadge = function() {
    const event = new CustomEvent('refreshNotifications');
    document.dispatchEvent(event);
};
