<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Créer une notification pour un utilisateur spécifique
     */
    public function createNotification($userId, string $message, string $url = null): Notification
    {
        return Notification::createForUser($userId, $message, $url);
    }

    /**
     * Créer des notifications pour plusieurs utilisateurs
     */
    public function createNotificationForUsers(array $userIds, string $message, string $url = null): bool
    {
        return Notification::createForUsers($userIds, $message, $url);
    }

    /**
     * Créer une notification pour tous les utilisateurs ayant un rôle spécifique
     */
    public function createNotificationForRole(string $roleName, string $message, string $url = null): bool
    {
        $role = Role::where('role_name', $roleName)->first();
        if (!$role) {
            return false;
        }

        $userIds = $role->users()->pluck('id')->toArray();
        return $this->createNotificationForUsers($userIds, $message, $url);
    }

    /**
     * Créer une notification pour tous les administrateurs
     */
    public function createNotificationForAdmins(string $message, string $url = null): bool
    {
        return $this->createNotificationForRole('admin', $message, $url);
    }

    /**
     * Créer une notification pour tous les gestionnaires
     */
    public function createNotificationForManagers(string $message, string $url = null): bool
    {
        return $this->createNotificationForRole('gestionnaire', $message, $url);
    }

    /**
     * Obtenir les notifications d'un utilisateur
     */
    public function getUserNotifications($userId, int $limit = 10): Collection
    {
        return Notification::forUser($userId)
            ->orderBy('date_notification', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir les notifications non lues d'un utilisateur
     */
    public function getUserUnreadNotifications($userId, int $limit = 10): Collection
    {
        return Notification::forUser($userId)
            ->unread()
            ->orderBy('date_notification', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir le nombre de notifications non lues pour un utilisateur
     */
    public function getUnreadCount($userId = null): int
    {
        $userId = $userId ?? Auth::id();
        return Notification::getUnreadCountForUser($userId);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(int $notificationId, $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Marquer toutes les notifications comme lues pour un utilisateur
     */
    public function markAllAsRead($userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        return Notification::markAllAsReadForUser($userId);
    }

    /**
     * Supprimer une notification
     */
    public function deleteNotification(int $notificationId, $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($notification) {
            return $notification->delete();
        }

        return false;
    }

    /**
     * Supprimer toutes les notifications lues d'un utilisateur
     */
    public function deleteReadNotifications($userId = null): int
    {
        $userId = $userId ?? Auth::id();
        return Notification::forUser($userId)->read()->delete();
    }

    /**
     * Notifications spécifiques pour les réformes
     */
    public function notifyReformeCreated($reformeId, string $reformeTitle, $createdBy = null): bool
    {
        $message = "Une nouvelle réforme '{$reformeTitle}' a été créée.";
        $url = route('reformes.show', $reformeId);
        
        // Notifier tous les gestionnaires et administrateurs
        $this->createNotificationForRole('admin', $message, $url);
        $this->createNotificationForRole('gestionnaire', $message, $url);
        
        return true;
    }

    public function notifyReformeUpdated($reformeId, string $reformeTitle, $updatedBy = null): bool
    {
        $message = "La réforme '{$reformeTitle}' a été mise à jour.";
        $url = route('reformes.show', $reformeId);
        
        // Notifier les superviseurs et gestionnaires
        $this->createNotificationForRole('admin', $message, $url);
        $this->createNotificationForRole('gestionnaire', $message, $url);
        $this->createNotificationForRole('superviseur', $message, $url);
        
        return true;
    }

    /**
     * Notifications spécifiques pour les activités
     */
    public function notifyActiviteCreated($activiteId, string $activiteTitle, $reformeTitle): bool
    {
        $message = "Une nouvelle activité '{$activiteTitle}' a été ajoutée à la réforme '{$reformeTitle}'.";
        $url = route('activites.show', $activiteId);
        
        // Notifier tous les utilisateurs concernés
        $this->createNotificationForRole('admin', $message, $url);
        $this->createNotificationForRole('gestionnaire', $message, $url);
        $this->createNotificationForRole('superviseur', $message, $url);
        
        return true;
    }

    public function notifyActiviteUpdated($activiteId, string $activiteTitle): bool
    {
        $message = "L'activité '{$activiteTitle}' a été mise à jour.";
        $url = route('activites.show', $activiteId);
        
        $this->createNotificationForRole('admin', $message, $url);
        $this->createNotificationForRole('gestionnaire', $message, $url);
        $this->createNotificationForRole('superviseur', $message, $url);
        
        return true;
    }

    /**
     * Notifications pour les suivis
     */
    public function notifySuiviCreated($suiviId, string $activiteTitle): bool
    {
        $message = "Un nouveau suivi a été ajouté pour l'activité '{$activiteTitle}'.";
        $url = route('suivi.show', $suiviId);
        
        $this->createNotificationForRole('admin', $message, $url);
        $this->createNotificationForRole('gestionnaire', $message, $url);
        $this->createNotificationForRole('superviseur', $message, $url);
        
        return true;
    }

    /**
     * Notifications pour les échéances
     */
    public function notifyDeadlineApproaching($activiteId, string $activiteTitle, int $daysLeft): bool
    {
        $message = "Échéance dans {$daysLeft} jour(s) pour l'activité '{$activiteTitle}'.";
        $url = route('activites.show', $activiteId);
        
        $this->createNotificationForRole('admin', $message, $url);
        $this->createNotificationForRole('gestionnaire', $message, $url);
        
        return true;
    }

    /**
     * Notifications système
     */
    public function notifySystemMaintenance(string $message): bool
    {
        // Notifier tous les utilisateurs
        $userIds = User::pluck('id')->toArray();
        return $this->createNotificationForUsers($userIds, "Maintenance système: {$message}");
    }

    /**
     * Nettoyer les anciennes notifications
     */
    public function cleanOldNotifications(): int
    {
        return Notification::cleanOldNotifications();
    }

    /**
     * Obtenir les statistiques des notifications
     */
    public function getNotificationStats($userId = null): array
    {
        $userId = $userId ?? Auth::id();
        
        return [
            'total' => Notification::forUser($userId)->count(),
            'unread' => Notification::forUser($userId)->unread()->count(),
            'read' => Notification::forUser($userId)->read()->count(),
            'recent' => Notification::forUser($userId)->recent()->count(),
        ];
    }
}
