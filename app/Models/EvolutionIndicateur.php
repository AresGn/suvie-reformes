<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EvolutionIndicateur extends Model
{
    protected $table = 'evolution_indicateurs';

    // Clé primaire composite
    protected $primaryKey = ['reforme_indicateur_id', 'date_evolution'];
    public $incrementing = false;

    protected $fillable = [
        'reforme_indicateur_id',
        'date_evolution',
        'valeur'
    ];

    protected $casts = [
        'date_evolution' => 'date',
        'valeur' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec l'indicateur de réforme
     */
    public function reformeIndicateur(): BelongsTo
    {
        return $this->belongsTo(ReformeIndicateur::class, 'reforme_indicateur_id');
    }

    /**
     * Obtenir la réforme via l'indicateur
     */
    public function reforme()
    {
        return $this->reformeIndicateur->reforme ?? null;
    }

    /**
     * Obtenir l'indicateur via la relation
     */
    public function indicateur()
    {
        return $this->reformeIndicateur->indicateur ?? null;
    }

    /**
     * Obtenir la valeur formatée avec l'unité
     */
    public function getValeurFormateeAttribute()
    {
        $indicateur = $this->indicateur();
        $unite = $indicateur ? $indicateur->unite : '';
        
        return number_format($this->valeur, 2, ',', ' ') . ' ' . $unite;
    }

    /**
     * Obtenir la date formatée en français
     */
    public function getDateFormateeAttribute()
    {
        return $this->date_evolution->format('d/m/Y');
    }

    /**
     * Obtenir le mois et l'année de l'évolution
     */
    public function getMoisAnneeAttribute()
    {
        return $this->date_evolution->format('m/Y');
    }

    /**
     * Vérifier si l'évolution est récente
     */
    public function getIsRecenteAttribute()
    {
        return $this->date_evolution->diffInDays(Carbon::now()) <= 30;
    }

    /**
     * Obtenir l'âge de l'évolution en jours
     */
    public function getAgeEnJoursAttribute()
    {
        return $this->date_evolution->diffInDays(Carbon::now());
    }

    /**
     * Obtenir l'évolution précédente
     */
    public function evolutionPrecedente()
    {
        return static::where('reforme_indicateur_id', $this->reforme_indicateur_id)
            ->where('date_evolution', '<', $this->date_evolution)
            ->orderBy('date_evolution', 'desc')
            ->first();
    }

    /**
     * Obtenir l'évolution suivante
     */
    public function evolutionSuivante()
    {
        return static::where('reforme_indicateur_id', $this->reforme_indicateur_id)
            ->where('date_evolution', '>', $this->date_evolution)
            ->orderBy('date_evolution', 'asc')
            ->first();
    }

    /**
     * Calculer la variation par rapport à l'évolution précédente
     */
    public function getVariationPrecedenteAttribute()
    {
        $precedente = $this->evolutionPrecedente();
        
        if (!$precedente || $precedente->valeur == 0) {
            return null;
        }

        return round((($this->valeur - $precedente->valeur) / $precedente->valeur) * 100, 2);
    }

    /**
     * Obtenir la variation absolue par rapport à l'évolution précédente
     */
    public function getVariationAbsolueAttribute()
    {
        $precedente = $this->evolutionPrecedente();
        
        if (!$precedente) {
            return null;
        }

        return $this->valeur - $precedente->valeur;
    }

    /**
     * Obtenir le type de variation (hausse, baisse, stable)
     */
    public function getTypeVariationAttribute()
    {
        $variation = $this->variation_precedente;
        
        if ($variation === null) {
            return 'initial';
        }
        
        if ($variation > 1) {
            return 'hausse';
        } elseif ($variation < -1) {
            return 'baisse';
        } else {
            return 'stable';
        }
    }

    /**
     * Obtenir l'icône de la variation
     */
    public function getIconeVariationAttribute()
    {
        switch ($this->type_variation) {
            case 'hausse':
                return 'fa-arrow-up text-success';
            case 'baisse':
                return 'fa-arrow-down text-danger';
            case 'stable':
                return 'fa-minus text-warning';
            default:
                return 'fa-circle text-info';
        }
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopePourPeriode($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_evolution', [$dateDebut, $dateFin]);
    }

    /**
     * Scope pour filtrer par année
     */
    public function scopePourAnnee($query, $annee)
    {
        return $query->whereYear('date_evolution', $annee);
    }

    /**
     * Scope pour filtrer par mois
     */
    public function scopePourMois($query, $annee, $mois)
    {
        return $query->whereYear('date_evolution', $annee)
                    ->whereMonth('date_evolution', $mois);
    }

    /**
     * Scope pour les évolutions récentes
     */
    public function scopeRecentes($query, $jours = 30)
    {
        $dateLimit = Carbon::now()->subDays($jours);
        return $query->where('date_evolution', '>=', $dateLimit);
    }

    /**
     * Scope pour ordonner par date
     */
    public function scopeOrderByDate($query, $direction = 'asc')
    {
        return $query->orderBy('date_evolution', $direction);
    }

    /**
     * Créer une nouvelle évolution avec validation
     */
    public static function creerEvolution($reformeIndicateurId, $valeur, $date = null)
    {
        $date = $date ?: Carbon::now()->format('Y-m-d');
        
        // Vérifier si une évolution existe déjà pour cette date
        $existante = static::where('reforme_indicateur_id', $reformeIndicateurId)
            ->where('date_evolution', $date)
            ->first();
            
        if ($existante) {
            // Mettre à jour la valeur existante
            $existante->update(['valeur' => $valeur]);
            return $existante;
        }
        
        // Créer une nouvelle évolution
        return static::create([
            'reforme_indicateur_id' => $reformeIndicateurId,
            'date_evolution' => $date,
            'valeur' => $valeur
        ]);
    }

    /**
     * Obtenir les statistiques pour une période
     */
    public static function statistiquesPourPeriode($reformeIndicateurId, $dateDebut, $dateFin)
    {
        $evolutions = static::where('reforme_indicateur_id', $reformeIndicateurId)
            ->pourPeriode($dateDebut, $dateFin)
            ->orderBy('date_evolution')
            ->get();

        if ($evolutions->isEmpty()) {
            return null;
        }

        $valeurs = $evolutions->pluck('valeur');
        
        return [
            'nombre' => $evolutions->count(),
            'valeur_min' => $valeurs->min(),
            'valeur_max' => $valeurs->max(),
            'valeur_moyenne' => round($valeurs->avg(), 2),
            'premiere_valeur' => $evolutions->first()->valeur,
            'derniere_valeur' => $evolutions->last()->valeur,
            'evolution_totale' => $evolutions->count() > 1 ? 
                round((($evolutions->last()->valeur - $evolutions->first()->valeur) / $evolutions->first()->valeur) * 100, 2) : 0
        ];
    }

    /**
     * Obtenir un résumé de l'évolution
     */
    public function getResumeAttribute()
    {
        return [
            'date' => $this->date_formatee,
            'valeur' => $this->valeur_formatee,
            'variation_precedente' => $this->variation_precedente,
            'variation_absolue' => $this->variation_absolue,
            'type_variation' => $this->type_variation,
            'age_jours' => $this->age_en_jours,
            'is_recente' => $this->is_recente
        ];
    }
}
