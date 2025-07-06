/**
 * Gestionnaire des notifications
 */
class NotificationManager {
    constructor() {
        this.baseUrl = '/notifications';
        this.refreshInterval = 30000; // 30 secondes
        this.intervalId = null;
        this.isDropdownOpen = false;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadNotifications();
        this.startAutoRefresh();
    }

    bindEvents() {
        // Événements du dropdown
        $('#notificationDropdown').on('click', (e) => {
            e.preventDefault();
            this.toggleDropdown();
        });

        // Marquer toutes comme lues
        $('#markAllRead').on('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.markAllAsRead();
        });

        // Fermer le dropdown en cliquant ailleurs
        $(document).on('click', (e) => {
            if (!$(e.target).closest('.notification-dropdown').length) {
                this.closeDropdown();
            }
        });

        // Événements pour la page des notifications
        if (window.location.pathname.includes('/notifications')) {
            this.bindPageEvents();
        }
    }

    bindPageEvents() {
        // Marquer comme lu/non lu
        $(document).on('click', '.mark-read-btn', (e) => {
            e.preventDefault();
            const notificationId = $(e.target).data('id');
            this.markAsRead(notificationId);
        });

        // Supprimer notification
        $(document).on('click', '.delete-notification-btn', (e) => {
            e.preventDefault();
            const notificationId = $(e.target).data('id');
            this.deleteNotification(notificationId);
        });

        // Marquer toutes comme lues
        $('#markAllReadPage').on('click', (e) => {
            e.preventDefault();
            this.markAllAsRead();
        });

        // Supprimer toutes les lues
        $('#deleteAllRead').on('click', (e) => {
            e.preventDefault();
            this.deleteAllRead();
        });
    }

    toggleDropdown() {
        if (this.isDropdownOpen) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }

    openDropdown() {
        $('#notificationDropdown').dropdown('show');
        this.isDropdownOpen = true;
        this.loadNotifications();
    }

    closeDropdown() {
        $('#notificationDropdown').dropdown('hide');
        this.isDropdownOpen = false;
    }

    async loadNotifications() {
        try {
            const response = await fetch(`${this.baseUrl}/api`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateNotificationCount(data.unread_count);
                this.renderNotifications(data.notifications);
            }
        } catch (error) {
            console.error('Erreur lors du chargement des notifications:', error);
        }
    }

    updateNotificationCount(count) {
        const countElement = $('#notificationCount');
        const titleElement = $('#notificationTitle');
        
        if (count > 0) {
            countElement.text(count).show();
            countElement.addClass('pulse');
            titleElement.text(`Notifications (${count})`);
            
            // Retirer l'animation après 600ms
            setTimeout(() => {
                countElement.removeClass('pulse');
            }, 600);
        } else {
            countElement.hide();
            titleElement.text('Notifications');
        }
    }

    renderNotifications(notifications) {
        const container = $('#notificationList');
        
        if (notifications.length === 0) {
            container.html(`
                <div class="notification-empty">
                    <i class="fas fa-bell-slash"></i>
                    <p>Aucune notification</p>
                </div>
            `);
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            const unreadClass = notification.is_read ? '' : 'unread';
            const iconClass = `notification-icon ${notification.color}`;
            
            html += `
                <div class="notification-item ${unreadClass}" data-id="${notification.id}" data-url="${notification.url || '#'}">
                    <div class="notification-content">
                        <div class="${iconClass}">
                            <i class="fas ${notification.icon}"></i>
                        </div>
                        <div class="notification-text">
                            <p class="notification-message">${notification.message}</p>
                            <p class="notification-time">${notification.time_ago}</p>
                            <div class="notification-actions">
                                ${!notification.is_read ? `<button class="btn btn-sm btn-primary mark-read-btn" data-id="${notification.id}">Marquer comme lu</button>` : ''}
                                <button class="btn btn-sm btn-danger delete-notification-btn" data-id="${notification.id}">Supprimer</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.html(html);

        // Ajouter les événements de clic
        container.find('.notification-item').on('click', (e) => {
            if (!$(e.target).hasClass('btn')) {
                const notificationId = $(e.currentTarget).data('id');
                const url = $(e.currentTarget).data('url');
                this.handleNotificationClick(notificationId, url);
            }
        });

        container.find('.mark-read-btn').on('click', (e) => {
            e.stopPropagation();
            const notificationId = $(e.target).data('id');
            this.markAsRead(notificationId);
        });

        container.find('.delete-notification-btn').on('click', (e) => {
            e.stopPropagation();
            const notificationId = $(e.target).data('id');
            this.deleteNotification(notificationId);
        });
    }

    async handleNotificationClick(notificationId, url) {
        // Marquer comme lue
        await this.markAsRead(notificationId);
        
        // Rediriger si URL fournie
        if (url && url !== '#') {
            window.location.href = url;
        }
        
        this.closeDropdown();
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch(`${this.baseUrl}/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateNotificationCount(data.unread_count);
                this.loadNotifications();
                this.showToast('Notification marquée comme lue', 'success');
            }
        } catch (error) {
            console.error('Erreur lors du marquage comme lu:', error);
            this.showToast('Erreur lors du marquage', 'error');
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch(`${this.baseUrl}/mark-all-read`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateNotificationCount(0);
                this.loadNotifications();
                this.showToast(data.message, 'success');
            }
        } catch (error) {
            console.error('Erreur lors du marquage global:', error);
            this.showToast('Erreur lors du marquage', 'error');
        }
    }

    async deleteNotification(notificationId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
            return;
        }

        try {
            const response = await fetch(`${this.baseUrl}/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateNotificationCount(data.unread_count);
                this.loadNotifications();
                this.showToast(data.message, 'success');
            }
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
            this.showToast('Erreur lors de la suppression', 'error');
        }
    }

    async deleteAllRead() {
        if (!confirm('Êtes-vous sûr de vouloir supprimer toutes les notifications lues ?')) {
            return;
        }

        try {
            const response = await fetch(`${this.baseUrl}/delete-read`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.loadNotifications();
                this.showToast(data.message, 'success');
            }
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
            this.showToast('Erreur lors de la suppression', 'error');
        }
    }

    showToast(message, type = 'info') {
        // Utiliser Lobibox si disponible
        if (typeof Lobibox !== 'undefined') {
            Lobibox.notify(type, {
                msg: message,
                size: 'mini',
                delay: 3000
            });
        } else {
            // Fallback simple
            alert(message);
        }
    }

    startAutoRefresh() {
        this.intervalId = setInterval(() => {
            if (!this.isDropdownOpen) {
                this.loadNotifications();
            }
        }, this.refreshInterval);
    }

    stopAutoRefresh() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }
}

// Initialiser le gestionnaire de notifications quand le DOM est prêt
$(document).ready(function() {
    window.notificationManager = new NotificationManager();
});
