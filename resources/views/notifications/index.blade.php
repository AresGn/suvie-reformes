@extends('layout.app')

@section('title', 'Notifications')

@section('content')
<div class="notifications-page">
    <div class="container-fluid">
        <div class="notifications-container">
            <!-- En-tête -->
            <div class="notifications-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i class="fas fa-bell mr-2"></i>
                        Mes Notifications
                    </h2>
                    <div>
                        <button class="btn btn-outline-primary btn-sm" onclick="window.notificationManager.loadNotifications()">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="notifications-stats">
                    <div class="stat-item">
                        <div class="stat-number">{{ $stats['total'] }}</div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number text-primary">{{ $stats['unread'] }}</div>
                        <div class="stat-label">Non lues</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number text-success">{{ $stats['read'] }}</div>
                        <div class="stat-label">Lues</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number text-info">{{ $stats['recent'] }}</div>
                        <div class="stat-label">Récentes</div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if($notifications->count() > 0)
            <div class="notification-actions-bar">
                <button class="btn-notification primary" id="markAllReadPage">
                    <i class="fas fa-check-double"></i> Marquer tout comme lu
                </button>
                <button class="btn-notification secondary" id="deleteAllRead">
                    <i class="fas fa-trash"></i> Supprimer les lues
                </button>
                <div class="ml-auto">
                    <select class="form-control form-control-sm" id="filterNotifications">
                        <option value="all">Toutes les notifications</option>
                        <option value="unread">Non lues uniquement</option>
                        <option value="read">Lues uniquement</option>
                    </select>
                </div>
            </div>
            @endif

            <!-- Liste des notifications -->
            <div class="notifications-list">
                @forelse($notifications as $notification)
                <div class="notification-list-item {{ $notification->isUnread() ? 'unread' : '' }}" 
                     data-id="{{ $notification->id }}" 
                     data-url="{{ $notification->url }}">
                    <div class="notification-content">
                        <div class="notification-icon {{ $notification->color }}">
                            <i class="fas {{ $notification->icon }}"></i>
                        </div>
                        <div class="notification-text flex-grow-1">
                            <p class="notification-message">{{ $notification->message }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="notification-time mb-0">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $notification->time_ago }}
                                </p>
                                <div class="notification-actions">
                                    @if($notification->isUnread())
                                    <button class="btn btn-sm btn-outline-primary mark-read-btn" 
                                            data-id="{{ $notification->id }}" 
                                            title="Marquer comme lu">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                    
                                    @if($notification->url)
                                    <a href="{{ route('notifications.show', $notification->id) }}" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="Voir le détail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif
                                    
                                    <button class="btn btn-sm btn-outline-danger delete-notification-btn" 
                                            data-id="{{ $notification->id }}" 
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="notification-empty">
                    <i class="fas fa-bell-slash"></i>
                    <h4>Aucune notification</h4>
                    <p class="text-muted">Vous n'avez aucune notification pour le moment.</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination si nécessaire -->
            @if($notifications->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal pour créer une notification de test (admin seulement) -->
@hasRole('admin')
<div class="modal fade" id="testNotificationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Créer une notification de test</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="testNotificationForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="testMessage">Message</label>
                        <textarea class="form-control" id="testMessage" name="message" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="testUrl">URL (optionnel)</label>
                        <input type="url" class="form-control" id="testUrl" name="url">
                    </div>
                    <div class="form-group">
                        <label for="testRole">Envoyer à</label>
                        <select class="form-control" id="testRole" name="role">
                            <option value="">Moi uniquement</option>
                            <option value="admin">Tous les administrateurs</option>
                            <option value="gestionnaire">Tous les gestionnaires</option>
                            <option value="superviseur">Tous les superviseurs</option>
                            <option value="utilisateur">Tous les utilisateurs</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bouton flottant pour les admins -->
<button class="btn btn-primary" 
        style="position: fixed; bottom: 20px; right: 20px; border-radius: 50%; width: 60px; height: 60px; z-index: 1000;"
        data-toggle="modal" 
        data-target="#testNotificationModal"
        title="Créer une notification de test">
    <i class="fas fa-plus"></i>
</button>
@endhasRole

@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/notifications-custom.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/notifications-manager.js') }}"></script>
<script>
$(document).ready(function() {
    // Filtrage des notifications
    $('#filterNotifications').on('change', function() {
        const filter = $(this).val();
        const items = $('.notification-list-item');
        
        items.show();
        
        if (filter === 'unread') {
            items.not('.unread').hide();
        } else if (filter === 'read') {
            items.filter('.unread').hide();
        }
    });

    // Formulaire de notification de test (admin)
    @hasRole('admin')
    $('#testNotificationForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            message: $('#testMessage').val(),
            url: $('#testUrl').val(),
            role: $('#testRole').val()
        };

        const endpoint = formData.role ? '/notifications/send-to-role' : '/notifications/test';
        
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#testNotificationModal').modal('hide');
                $('#testNotificationForm')[0].reset();
                window.notificationManager.showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.notificationManager.showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.notificationManager.showToast('Erreur lors de l\'envoi', 'error');
        });
    });
    @endhasRole
});
</script>
@endsection
