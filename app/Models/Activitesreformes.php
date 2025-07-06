<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Activitesreformes extends Model
{
    protected $table = 'activites_reformes';

    protected $fillable = [
        'reforme_id',
        'libelle',
        'date_debut',
        'date_fin_prevue',
        'date_fin',
        'poids',
        'parent',
        'structure_responsable',
        'created_by',
        'updated_by'
    ];

    /**
     * Attributs avec valeurs par défaut
     */
    protected $attributes = [
        'statut' => 'C', // Statut par défaut : Créé
    ];

    // Relation avec la réforme
    public function reforme()
    {
        return $this->belongsTo(Reforme::class, 'reforme_id');
    }

    // Relation avec l'activité parente (hiérarchie)
    public function parentActivite()
    {
        return $this->belongsTo(Activitesreformes::class, 'parent');
    }

    // Relation avec les sous-activités
    public function sousActivites()
    {
        return $this->hasMany(Activitesreformes::class, 'parent');
    }

    // Relation avec l'utilisateur créateur
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relation avec l'utilisateur qui a fait la dernière modification
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Relation avec les suivis d'activités
    public function suivis()
    {
        return $this->hasMany(SuiviActivites::class, 'activite_reforme_id');
    }

    // Relation pour récupérer le dernier suivi (plus efficace pour les requêtes)
    public function dernierSuiviRelation()
    {
        return $this->hasOne(SuiviActivites::class, 'activite_reforme_id')->latestOfMany();
    }

    // Méthode pour récupérer le dernier suivi d'activité
    public function dernierSuivi()
    {
        return $this->suivis()->orderBy('created_at', 'desc')->first();
    }



    // Accesseur pour le statut lisible
    public function getStatutLabelAttribute()
    {
        $statuts = [
            'C' => 'En cours',
            'P' => 'En Pause',
            'A' => 'Achevé'
        ];
        
        return $statuts[$this->statut] ?? 'Inconnu';
    }

    // Scope pour les activités principales (sans parent)
    public function scopePrincipales($query)
    {
        return $query->whereNull('parent');
    }

    // Scope pour les sous-activités
    public function scopeSousActivites($query)
    {
        return $query->whereNotNull('parent');
    }

    /**
     * Calculer le pourcentage d'avancement de l'activité
     */
    public function getAvancementAttribute()
    {
        if ($this->statut == 'A') {
            return 100;
        } elseif ($this->statut == 'C') {
            return 0;
        } else {
            // Calculer en fonction du nombre de suivis et de la date
            $totalDays = max(1, (strtotime($this->date_fin_prevue) - strtotime($this->date_debut)) / 86400);
            $elapsedDays = min($totalDays, max(0, (time() - strtotime($this->date_debut)) / 86400));
            $timeProgress = min(90, round(($elapsedDays / $totalDays) * 100));
            
            // Ajuster en fonction du nombre de suivis
            $suiviCount = $this->suivis->count();
            $suiviBonus = min(10, $suiviCount * 2); // 2% par suivi, max 10%
            
            return min(95, $timeProgress + $suiviBonus);
        }
    }

    /**
     * Obtenir le badge HTML pour l'affichage du statut avec couleurs Bootstrap 3.4
     *
     * @return string HTML du badge de statut
     */
    public function getStatusBadgeAttribute()
    {
        $statusMap = [
            'C' => ['class' => 'label-warning', 'text' => 'En cours'],
            'P' => ['class' => 'label-default', 'text' => 'En Pause'],
            'A' => ['class' => 'label-success', 'text' => 'Achevé']
        ];

        $status = $statusMap[$this->statut] ?? ['class' => 'label-default', 'text' => 'Inconnu'];
        return '<span class="label ' . $status['class'] . '">' . $status['text'] . '</span>';
    }

    /**
     * Obtenir le texte du statut sans HTML
     *
     * @return string Texte du statut
     */
    public function getStatusTextAttribute()
    {
        $statusMap = [
            'C' => 'Créé',
            'P' => 'En cours',
            'A' => 'Achevé'
        ];

        return $statusMap[$this->statut] ?? 'Inconnu';
    }

    /**
     * Obtenir la classe CSS Bootstrap 3.4 pour le statut
     *
     * @return string Classe CSS
     */
    public function getStatusClassAttribute()
    {
        $statusMap = [
            'C' => 'label-default',
            'P' => 'label-warning',
            'A' => 'label-success'
        ];

        return $statusMap[$this->statut] ?? 'label-default';
    }

    /**
     * Mettre à jour le statut de manière sécurisée (pour le système de cascade)
     *
     * @param string $nouveauStatut
     * @param int|null $userId
     * @return bool
     */
    public function updateStatut($nouveauStatut, $userId = null)
    {
        // Vérifier que le statut est valide
        if (!in_array($nouveauStatut, ['C', 'P', 'A'])) {
            return false;
        }

        // Mettre à jour directement les attributs pour contourner fillable
        $this->statut = $nouveauStatut;
        $this->updated_by = $userId ?? Auth::id();
        if ($nouveauStatut === 'A') {
            $this->date_fin = now();
        }

        return $this->save();
    }

    /**
     * Démarrer une activité (passage de C à P)
     *
     * @param int|null $userId
     * @return bool
     */
    public function demarrer($userId = null)
    {
        if ($this->statut === 'C') {
            return $this->updateStatut('P', $userId);
        }
        return false;
    }

    /**
     * Terminer une activité (passage à A)
     *
     * @param int|null $userId
     * @return bool
     */
    public function terminer($userId = null)
    {
        if (in_array($this->statut, ['C', 'P'])) {
            return $this->updateStatut('A', $userId);
        }
        return false;
    }
}



