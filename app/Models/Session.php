<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Session extends Model
{
    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
        'status',
        'last_activity'
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'last_activity' => 'datetime',
    ];

    /**
     * Relation avec le modèle User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les sessions actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour les sessions inactives
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope pour les sessions d'un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour les sessions récentes
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('login_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Obtenir la durée de la session en minutes
     */
    public function getDurationAttribute()
    {
        if (!$this->logout_at) {
            return $this->last_activity ? 
                $this->login_at->diffInMinutes($this->last_activity) : 
                $this->login_at->diffInMinutes(Carbon::now());
        }
        
        return $this->login_at->diffInMinutes($this->logout_at);
    }

    /**
     * Obtenir la durée formatée
     */
    public function getFormattedDurationAttribute()
    {
        $minutes = $this->duration;
        
        if ($minutes < 60) {
            return $minutes . ' min';
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($hours < 24) {
            return $hours . 'h ' . $remainingMinutes . 'min';
        }
        
        $days = floor($hours / 24);
        $remainingHours = $hours % 24;
        
        return $days . 'j ' . $remainingHours . 'h';
    }

    /**
     * Vérifier si la session est active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Marquer la session comme inactive
     */
    public function markAsInactive()
    {
        $this->update([
            'status' => 'inactive',
            'logout_at' => Carbon::now()
        ]);
    }

    /**
     * Mettre à jour l'activité de la session
     */
    public function updateActivity()
    {
        $this->update([
            'last_activity' => Carbon::now()
        ]);
    }

    /**
     * Créer une nouvelle session
     */
    public static function createSession($user, $request)
    {
        return self::create([
            'user_id' => $user->id,
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent', ''),
            'login_at' => Carbon::now(),
            'last_activity' => Carbon::now(),
            'status' => 'active'
        ]);
    }

    /**
     * Nettoyer les anciennes sessions (à exécuter périodiquement)
     */
    public static function cleanupOldSessions($daysToKeep = 90)
    {
        return self::where('login_at', '<', Carbon::now()->subDays($daysToKeep))->delete();
    }

    /**
     * Marquer les sessions inactives comme fermées
     */
    public static function markInactiveSessions($hoursInactive = 24)
    {
        $cutoffTime = Carbon::now()->subHours($hoursInactive);
        
        return self::where('status', 'active')
            ->where(function ($query) use ($cutoffTime) {
                $query->where('last_activity', '<', $cutoffTime)
                      ->orWhere(function ($q) use ($cutoffTime) {
                          $q->whereNull('last_activity')
                            ->where('login_at', '<', $cutoffTime);
                      });
            })
            ->update([
                'status' => 'inactive',
                'logout_at' => Carbon::now()
            ]);
    }
}
