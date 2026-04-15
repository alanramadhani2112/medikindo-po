/**
 * Notification System
 * Auto-refresh notifications every 30 seconds
 */

class NotificationManager {
    constructor() {
        this.refreshInterval = 30000; // 30 seconds
        this.init();
    }

    init() {
        // Auto refresh notifications
        setInterval(() => this.refreshNotifications(), this.refreshInterval);
        
        // Initial load
        this.refreshNotifications();
    }

    async refreshNotifications() {
        try {
            const response = await fetch('/notifications/recent');
            const data = await response.json();
            
            // Update badge count
            this.updateBadgeCount(data.unread_count);
            
        } catch (error) {
            console.error('Failed to refresh notifications:', error);
        }
    }

    updateBadgeCount(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    new NotificationManager();
});
