<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ReformeIndicateur extends Model
{
    protected $table = 'reformes_indicateurs';

    protected $fillable = [
        'reforme_id',
        'indicateur_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec la réforme
     */
    public function reforme(): BelongsTo
    {
        return $this->belongsTo(Reforme::class, 'reforme_id');
    }

    /**
     * Relation avec l'indicateur
     */
    public function indicateur(): BelongsTo
    {
        return $this->belongsTo(Indicateur::class, 'indicateur_id');
    }

    /**
     * Relation avec les évolutions de l'indicateur
     */
    public function evolutions(): HasMany
    {
        return $this->hasMany(EvolutionIndicateur::class, 'reforme_indicateur_id');
    }

    /**
     * Obtenir les évolutions ordonnées par date
     */
    public function evolutionsOrdered(): HasMany
    {
        return $this->evolutions()->orderBy('date_evolution', 'desc');
    }

    /**
     * Obtenir la dernière évolution
     */
    public function derniereEvolution()
    {
        return $this->evolutions()->latest('date_evolution')->first();
    }

    /**
     * Obtenir la première évolution (valeur initiale)
     */
    public function premiereEvolution()
    {
        return $this->evolutions()->oldest('date_evolution')->first();
    }

    /**
     * Obtenir la valeur actuelle de l'indicateur
     */
    public function getValeurActuelleAttribute()
    {
        $derniere = $this->derniereEvolution();
        return $derniere ? $derniere->valeur : null;
    }

    /**
     * Obtenir la valeur initiale de l'indicateur
     */
    public function getValeurInitialeAttribute()
    {
        $premiere = $this->premiereEvolution();
        return $premiere ? $premiere->valeur : null;
    }

    /**
     * Calculer l'évolution en pourcentage
     */
    public function getEvolutionPourcentageAttribute()
    {
        $valeurInitiale = $this->valeur_initiale;
        $valeurActuelle = $this->valeur_actuelle;

        if (!$valeurInitiale || !$valeurActuelle || $valeurInitiale == 0) {
            return null;
        }

        return round((($valeurActuelle - $valeurInitiale) / $valeurInitiale) * 100, 2);
    }

    /**
     * Obtenir la tendance de l'indicateur
     */
    public function getTendanceAttribute()
    {
        $evolution = $this->evolution_pourcentage;
        
        if ($evolution === null) {
            return 'stable';
        }
        
        if ($evolution > 5) {
            return 'hausse';
        } elseif ($evolution < -5) {
            return 'baisse';
        } else {
            return 'stable';
        }
    }

    /**
     * Obtenir l'icône de la tendance
     */
    public function getIconeTendanceAttribute()
    {
        switch ($this->tendance) {
            case 'hausse':
                return 'fa-arrow-up text-success';
            case 'baisse':
                return 'fa-arrow-down text-danger';
            default:
                return 'fa-minus text-warning';
        }
    }

    /**
     * Obtenir les évolutions pour une période donnée
     */
    public function evolutionsPourPeriode($dateDebut, $dateFin)
    {
        return $this->evolutions()
            ->whereBetween('date_evolution', [$dateDebut, $dateFin])
            ->orderBy('date_evolution')
            ->get();
    }

    /**
     * Obtenir les évolutions des 12 derniers mois
     */
    public function evolutionsDerniersMois($nombreMois = 12)
    {
        $dateDebut = Carbon::now()->subMonths($nombreMois);
        return $this->evolutionsPourPeriode($dateDebut, Carbon::now());
    }

    /**
     * Vérifier si l'indicateur a des données récentes
     */
    public function hasDataRecente($jours = 30)
    {
        $dateLimit = Carbon::now()->subDays($jours);
        return $this->evolutions()->where('date_evolution', '>=', $dateLimit)->exists();
    }

    /**
     * Obtenir le nombre total d'évolutions
     */
    public function getNombreEvolutionsAttribute()
    {
        return $this->evolutions()->count();
    }

    /**
     * Scope pour filtrer par réforme
     */
    public function scopeForReforme($query, $reformeId)
    {
        return $query->where('reforme_id', $reformeId);
    }

    /**
     * Scope pour filtrer par indicateur
     */
    public function scopeForIndicateur($query, $indicateurId)
    {
        return $query->where('indicateur_id', $indicateurId);
    }

    /**
     * Scope pour les indicateurs avec données récentes
     */
    public function scopeWithRecentData($query, $jours = 30)
    {
        $dateLimit = Carbon::now()->subDays($jours);
        return $query->whereHas('evolutions', function($q) use ($dateLimit) {
            $q->where('date_evolution', '>=', $dateLimit);
        });
    }

    /**
     * Créer une nouvelle évolution pour cet indicateur
     */
    public function ajouterEvolution($valeur, $date = null)
    {
        $date = $date ?: Carbon::now()->format('Y-m-d');
        
        return EvolutionIndicateur::create([
            'reforme_indicateur_id' => $this->id,
            'date_evolution' => $date,
            'valeur' => $valeur
        ]);
    }

    /**
     * Obtenir un résumé de l'indicateur
     */
    public function getResumeAttribute()
    {
        return [
            'reforme' => $this->reforme->titre ?? 'N/A',
            'indicateur' => $this->indicateur->libelle ?? 'N/A',
            'unite' => $this->indicateur->unite ?? '',
            'valeur_actuelle' => $this->valeur_actuelle,
            'valeur_initiale' => $this->valeur_initiale,
            'evolution_pourcentage' => $this->evolution_pourcentage,
            'tendance' => $this->tendance,
            'nombre_evolutions' => $this->nombre_evolutions,
            'derniere_mise_a_jour' => $this->derniereEvolution()?->date_evolution
        ];
    }
}
