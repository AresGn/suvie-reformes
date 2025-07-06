<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'message',
        'url',
        'date_notification',
        'statut'
    ];

    protected $casts = [
        'date_notification' => 'datetime',
    ];

    // Constantes pour les statuts
    const STATUT_NON_LU = 'N';
    const STATUT_LU = 'L';

    // Types de notifications (pour catégoriser)
    const TYPE_REFORME_CREATED = 'reforme_created';
    const TYPE_REFORME_UPDATED = 'reforme_updated';
    const TYPE_ACTIVITE_CREATED = 'activite_created';
    const TYPE_ACTIVITE_UPDATED = 'activite_updated';
    const TYPE_SUIVI_CREATED = 'suivi_created';
    const TYPE_DEADLINE_APPROACHING = 'deadline_approaching';
    const TYPE_SYSTEM = 'system';

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les notifications non lues
     */
    public function scopeUnread($query)
    {
        return $query->where('statut', self::STATUT_NON_LU);
    }

    /**
     * Scope pour les notifications lues
     */
    public function scopeRead($query)
    {
        return $query->where('statut', self::STATUT_LU);
    }

    /**
     * Scope pour les notifications récentes (dernières 30 jours)
     */
    public function scopeRecent($query)
    {
        return $query->where('date_notification', '>=', Carbon::now()->subDays(30));
    }

    /**
     * Scope pour un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Marquer la notification comme lue
     */
    public function markAsRead()
    {
        $this->update(['statut' => self::STATUT_LU]);
    }

    /**
     * Marquer la notification comme non lue
     */
    public function markAsUnread()
    {
        $this->update(['statut' => self::STATUT_NON_LU]);
    }

    /**
     * Vérifier si la notification est lue
     */
    public function isRead(): bool
    {
        return $this->statut === self::STATUT_LU;
    }

    /**
     * Vérifier si la notification est non lue
     */
    public function isUnread(): bool
    {
        return $this->statut === self::STATUT_NON_LU;
    }

    /**
     * Obtenir le temps écoulé depuis la notification
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->date_notification->diffForHumans();
    }

    /**
     * Obtenir l'icône selon le type de notification (basé sur le message)
     */
    public function getIconAttribute(): string
    {
        $message = strtolower($this->message);
        
        if (str_contains($message, 'réforme')) {
            return 'fa-file-text';
        } elseif (str_contains($message, 'activité')) {
            return 'fa-tasks';
        } elseif (str_contains($message, 'suivi')) {
            return 'fa-eye';
        } elseif (str_contains($message, 'échéance') || str_contains($message, 'deadline')) {
            return 'fa-clock-o';
        } elseif (str_contains($message, 'système')) {
            return 'fa-cog';
        } else {
            return 'fa-bell';
        }
    }

    /**
     * Obtenir la couleur selon le type de notification
     */
    public function getColorAttribute(): string
    {
        $message = strtolower($this->message);
        
        if (str_contains($message, 'créé') || str_contains($message, 'ajouté')) {
            return 'success';
        } elseif (str_contains($message, 'modifié') || str_contains($message, 'mis à jour')) {
            return 'info';
        } elseif (str_contains($message, 'échéance') || str_contains($message, 'deadline')) {
            return 'warning';
        } elseif (str_contains($message, 'erreur') || str_contains($message, 'problème')) {
            return 'danger';
        } else {
            return 'primary';
        }
    }

    /**
     * Créer une notification pour un utilisateur
     */
    public static function createForUser($userId, $message, $url = null, $dateNotification = null)
    {
        return self::create([
            'user_id' => $userId,
            'message' => $message,
            'url' => $url,
            'date_notification' => $dateNotification ?? now(),
            'statut' => self::STATUT_NON_LU
        ]);
    }

    /**
     * Créer des notifications pour plusieurs utilisateurs
     */
    public static function createForUsers(array $userIds, $message, $url = null, $dateNotification = null)
    {
        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'message' => $message,
                'url' => $url,
                'date_notification' => $dateNotification ?? now(),
                'statut' => self::STATUT_NON_LU,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        return self::insert($notifications);
    }

    /**
     * Supprimer les anciennes notifications (plus de 90 jours)
     */
    public static function cleanOldNotifications()
    {
        return self::where('date_notification', '<', Carbon::now()->subDays(90))->delete();
    }

    /**
     * Obtenir le nombre de notifications non lues pour un utilisateur
     */
    public static function getUnreadCountForUser($userId): int
    {
        return self::forUser($userId)->unread()->count();
    }

    /**
     * Marquer toutes les notifications comme lues pour un utilisateur
     */
    public static function markAllAsReadForUser($userId)
    {
        return self::forUser($userId)->unread()->update(['statut' => self::STATUT_LU]);
    }
}
