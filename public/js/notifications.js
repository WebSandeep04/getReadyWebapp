$(document).ready(function() {
    let notificationsLoaded = false;
    
    // Load notifications when dropdown is opened
    $('#notification-toggle').on('click', function() {
        if (!notificationsLoaded) {
            loadNotifications();
        }
    });
    
    // Mark all notifications as read
    $('#mark-all-read').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        $.ajax({
            url: '/notifications/mark-all-read',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    updateNotificationBadge(0);
                    loadNotifications(); // Reload to update read status
                }
            },
            error: function() {
                console.error('Failed to mark all notifications as read');
            }
        });
    });
    
    // Load notifications from server
    function loadNotifications() {
        $.ajax({
            url: '/notifications',
            type: 'GET',
            success: function(response) {
                displayNotifications(response.notifications);
                updateNotificationBadge(response.unreadCount);
                notificationsLoaded = true;
            },
            error: function() {
                displayNotifications([]);
                console.error('Failed to load notifications');
            }
        });
    }
    
    // Display notifications in dropdown
    function displayNotifications(notifications) {
        const $list = $('#notifications-list');
        
        if (notifications.length === 0) {
            $list.html(`
                <div class="text-center py-3">
                    <i class="bi bi-bell text-muted" style="font-size: 2rem;"></i>
                    <div class="mt-2 text-muted">No notifications</div>
                </div>
            `);
            return;
        }
        
        let html = '';
        notifications.forEach(function(notification) {
            const timeAgo = getTimeAgo(notification.created_at);
            const bgClass = getNotificationBgClass(notification.type);
            const iconClass = notification.icon || getDefaultIcon(notification.type);
            
            // Check if this is a rejection notification with cloth data
            const isRejectionNotification = notification.data && notification.data.cloth_id && notification.data.reject_reason;
            
            html += `
                <a class="notification-item ${notification.read ? 'read' : 'unread'}" 
                   href="#" 
                   data-notification-id="${notification.id}"
                   ${isRejectionNotification ? `data-cloth-id="${notification.data.cloth_id}" data-reject-reason="${notification.data.reject_reason}"` : ''}>
                    <div class="d-flex align-items-start gap-3">
                        <div class="notification-icon-wrapper ${bgClass}">
                            <i class="${iconClass}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="notification-title">${notification.title}</div>
                                ${!notification.read ? '<div class="unread-dot mt-1"></div>' : ''}
                            </div>
                            <div class="notification-msg">${notification.message}</div>
                            <div class="notification-time">${timeAgo}</div>
                        </div>
                    </div>
                </a>
            `;
        });
        
        $list.html(html);
        
        // Add click handlers for individual notifications
        $('.notification-item').on('click', function(e) {
            e.preventDefault();
            const notificationId = $(this).data('notification-id');
            const clothId = $(this).data('cloth-id');
            const rejectReason = $(this).data('reject-reason');
            
            // Mark notification as read
            markNotificationAsRead(notificationId);
            
            // If this is a rejection notification, navigate to rejection management page
            if (clothId && rejectReason && clothId !== 'undefined' && rejectReason !== 'undefined') {
                try {
                    // Show loading state
                    const $notificationItem = $(this);
                    $notificationItem.find('.notification-title').html('<i class="bi bi-hourglass-split me-1 small"></i>Redirecting...');
                    
                    // Navigate to the rejection management page
                    setTimeout(function() {
                        window.location.href = `/rejections/${clothId}`;
                    }, 400);
                } catch (error) {
                    console.error('Error navigating to rejection page:', error);
                }
            }
        });
    }
    
    // Mark individual notification as read
    function markNotificationAsRead(notificationId) {
        $.ajax({
            url: '/notifications/mark-read',
            type: 'POST',
            data: {
                notification_id: notificationId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    updateNotificationBadge(response.unreadCount);
                    // Update the notification item to show as read
                    $(`.notification-item[data-notification-id="${notificationId}"]`)
                        .removeClass('unread')
                        .addClass('read')
                        .find('.unread-dot')
                        .remove();
                }
            },
            error: function() {
                console.error('Failed to mark notification as read');
            }
        });
    }
    
    // Update notification badge count
    function updateNotificationBadge(count) {
        const $badge = $('#notification-badge');
        if (count > 0) {
            $badge.text(count).show();
        } else {
            $badge.hide();
        }
    }
    
    // Get background class based on notification type
    function getNotificationBgClass(type) {
        switch (type) {
            case 'success': return 'icon-bg-success';
            case 'warning': return 'icon-bg-warning';
            case 'danger': return 'icon-bg-danger';
            case 'info': 
            default: return 'icon-bg-info';
        }
    }
    
    // Get default icon based on notification type
    function getDefaultIcon(type) {
        switch (type) {
            case 'success': return 'bi bi-check-lg';
            case 'warning': return 'bi bi-exclamation-triangle';
            case 'danger': return 'bi bi-x-lg';
            case 'info': 
            default: return 'bi bi-info-circle';
        }
    }
    
    // Get time ago string
    function getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) {
            return 'Just now';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        } else {
            const days = Math.floor(diffInSeconds / 86400);
            return `${days} day${days > 1 ? 's' : ''} ago`;
        }
    }
    
    // Auto-refresh notifications every 30 seconds
    setInterval(function() {
        if (notificationsLoaded) {
            $.ajax({
                url: '/notifications/unread-count',
                type: 'GET',
                success: function(response) {
                    updateNotificationBadge(response.unreadCount);
                }
            });
        }
    }, 30000);
});

// Add CSS for notification styling
const style = document.createElement('style');
style.textContent = `
    .notification-item.unread {
        background-color: #f8f9fa;
    }
    
    .notification-item.read {
        opacity: 0.7;
    }
    
    .notification-item:hover {
        background-color: #e9ecef;
    }
    
    .notification-item .badge {
        font-size: 0.6rem;
    }
`;
document.head.appendChild(style); 