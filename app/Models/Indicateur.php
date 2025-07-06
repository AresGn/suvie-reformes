<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indicateur extends Model
{
    protected $table = 'indicateurs';

    protected $fillable = [
        'libelle',
        'unite'
    ];

    /**
     * Relation avec les réformes (many-to-many)
     */
    public function reformes(): BelongsToMany
    {
        return $this->belongsToMany(Reforme::class, 'reformes_indicateurs');
    }

    /**
     * Relation avec les indicateurs de réforme
     */
    public function reformeIndicateurs(): HasMany
    {
        return $this->hasMany(ReformeIndicateur::class, 'indicateur_id');
    }

    /**
     * Obtenir toutes les évolutions de cet indicateur
     */
    public function evolutions()
    {
        return EvolutionIndicateur::whereHas('reformeIndicateur', function($query) {
            $query->where('indicateur_id', $this->id);
        });
    }

    /**
     * Obtenir le nombre de réformes utilisant cet indicateur
     */
    public function getNombreReformesAttribute()
    {
        return $this->reformes()->count();
    }

    /**
     * Vérifier si l'indicateur est utilisé
     */
    public function getIsUtiliseAttribute()
    {
        return $this->nombre_reformes > 0;
    }

    /**
     * Obtenir la dernière évolution de cet indicateur
     */
    public function getDerniereEvolutionAttribute()
    {
        return $this->evolutions()->latest('date_evolution')->first();
    }

    /**
     * Scope pour les indicateurs utilisés
     */
    public function scopeUtilises($query)
    {
        return $query->whereHas('reformes');
    }

    /**
     * Scope pour les indicateurs non utilisés
     */
    public function scopeNonUtilises($query)
    {
        return $query->whereDoesntHave('reformes');
    }
}
