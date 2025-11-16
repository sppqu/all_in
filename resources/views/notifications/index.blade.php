@extends('layouts.adminty')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fa fa-bell me-2"></i>
                        Notifikasi Sistem
                    </h4>
                    <div>
                        <button class="btn btn-success btn-sm" onclick="markAllAsRead()">
                            <i class="fa fa-check me-1"></i>
                            Tandai Semua Dibaca
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="notificationsContainer">
                        <div class="text-center py-4">
                            <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                            <p class="mt-2 text-muted">Memuat notifikasi...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAllNotifications();
});

function loadAllNotifications() {
    fetch('{{ route("manage.notifications.index") }}')
        .then(response => response.json())
        .then(data => {
            displayNotifications(data.data);
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            document.getElementById('notificationsContainer').innerHTML = `
                <div class="text-center py-4">
                    <i class="fa fa-exclamation-triangle fa-2x text-danger"></i>
                    <p class="mt-2 text-danger">Gagal memuat notifikasi</p>
                </div>
            `;
        });
}

function displayNotifications(notifications) {
    const container = document.getElementById('notificationsContainer');
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fa fa-bell-slash fa-2x text-muted"></i>
                <p class="mt-2 text-muted">Tidak ada notifikasi</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = notifications.map(notification => `
        <div class="notification-item border-bottom py-3 ${notification.is_read ? 'opacity-75' : ''}" data-id="${notification.id}">
            <div class="d-flex align-items-start">
                <div class="flex-shrink-0 me-3">
                    <div class="notification-icon">
                        <i class="fa ${notification.icon} fa-2x text-${notification.color}"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 ${notification.is_read ? 'text-muted' : 'fw-bold'}">${notification.title}</h6>
                            <p class="mb-1">${notification.message}</p>
                            <small class="text-muted">
                                <i class="fa fa-clock me-1"></i>
                                ${formatDateTime(notification.created_at)}
                            </small>
                        </div>
                        <div class="flex-shrink-0 ms-2">
                            ${notification.is_read ? 
                                '<span class="badge bg-secondary">Dibaca</span>' : 
                                '<button class="btn btn-sm btn-outline-success" onclick="markAsRead(' + notification.id + ')">Tandai Dibaca</button>'
                            }
                            <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteNotification(${notification.id})">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function markAsRead(id) {
    fetch(`{{ url('manage/notifications') }}/${id}/read`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadAllNotifications(); // Reload notifications
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllAsRead() {
    fetch('{{ route("manage.notifications.read-all") }}', {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadAllNotifications(); // Reload notifications
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

function deleteNotification(id) {
    if (confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')) {
        fetch(`{{ url('manage/notifications') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadAllNotifications(); // Reload notifications
            }
        })
        .catch(error => {
            console.error('Error deleting notification:', error);
        });
    }
}

function formatDateTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return 'Baru saja';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} menit yang lalu`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} jam yang lalu`;
    } else {
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}
</script>

<style>
.notification-item {
    transition: all 0.3s ease;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #f8f9fa;
}

.notification-item:last-child {
    border-bottom: none !important;
}
</style>
@endsection
