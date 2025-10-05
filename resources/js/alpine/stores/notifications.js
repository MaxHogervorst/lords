/**
 * Notifications Store
 * Manages application-wide notifications/alerts
 */
export default {
    items: [],

    /**
     * Add a new notification
     * @param {Object} notification - { text, type, title, duration }
     */
    add(notification) {
        const id = Date.now() + Math.random();
        const duration = notification.duration || 3000;

        const item = {
            id,
            text: notification.text || '',
            type: notification.type || 'info', // success, error, info, warning
            title: notification.title || '',
            duration
        };

        this.items.push(item);

        // Auto-remove after duration
        if (duration > 0) {
            setTimeout(() => this.remove(id), duration);
        }

        return id;
    },

    /**
     * Remove a notification by ID
     */
    remove(id) {
        this.items = this.items.filter(n => n.id !== id);
    },

    /**
     * Clear all notifications
     */
    clear() {
        this.items = [];
    },

    /**
     * Add success notification
     */
    success(text, title = 'Success') {
        return this.add({ text, type: 'success', title });
    },

    /**
     * Add error notification
     */
    error(text, title = 'Error') {
        return this.add({ text, type: 'error', title, duration: 5000 });
    },

    /**
     * Add info notification
     */
    info(text, title = 'Info') {
        return this.add({ text, type: 'info', title });
    },

    /**
     * Add warning notification
     */
    warning(text, title = 'Warning') {
        return this.add({ text, type: 'warning', title, duration: 4000 });
    }
};
