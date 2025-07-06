<?php

namespace App\Models;
use App\Models\Typereforme;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Reforme extends Model
{
    protected $table ='reformes';

    protected $fillable = [
        'id',
        'titre',
        'objectifs',
        'budget',
        'date_debut',
        'date_fin_prevue',
        'date_fin',
        'pieces_justificatifs',
        'type_reforme',
        'created_by',
        'updated_by'
    ];

    // Relation avec le type de réforme
    public function type()
    {
        return $this->belongsTo(Typereforme::class, 'type_reforme');
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

    // Relation avec les structures liées à la réforme
    public function structures()
    {
        return $this->belongsToMany(Structure::class, 'reforme_structure');
    }

    // Relation avec les indicateurs liés à la réforme
    public function indicateurs()
    {
        return $this->belongsToMany(Indicateur::class, 'reformes_indicateurs');
    }

    // Relation avec les indicateurs de réforme (table pivot enrichie)
    public function reformeIndicateurs()
    {
        return $this->hasMany(ReformeIndicateur::class, 'reforme_id');
    }

    // Obtenir toutes les évolutions des indicateurs de cette réforme
    public function evolutionsIndicateurs()
    {
        return EvolutionIndicateur::whereHas('reformeIndicateur', function($query) {
            $query->where('reforme_id', $this->id);
        });
    }

    // Accesseur pour le statut (calculé selon les dates)
    public function getStatutAttribute()
    {
        if ($this->date_fin) {
            return 'Achevé';
        } elseif ($this->date_debut && $this->date_debut <= now()) {
            return 'En cours';
        } elseif ($this->date_debut) {
            return 'Planifié';
        } else {
            return 'Brouillon';
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
            'Brouillon' => ['class' => 'label-default', 'text' => 'Brouillon'],
            'Planifié' => ['class' => 'label-info', 'text' => 'Planifié'],
            'En cours' => ['class' => 'label-warning', 'text' => 'En cours'],
            'Achevé' => ['class' => 'label-success', 'text' => 'Terminé']
        ];

        $statut = $this->statut;
        $status = $statusMap[$statut] ?? ['class' => 'label-default', 'text' => $statut];
        return '<span class="label ' . $status['class'] . '">' . $status['text'] . '</span>';
    }

    /**
     * Obtenir la classe CSS Bootstrap 3.4 pour le statut
     *
     * @return string Classe CSS
     */
    public function getStatusClassAttribute()
    {
        $statusMap = [
            'Brouillon' => 'label-default',
            'Planifié' => 'label-info',
            'En cours' => 'label-warning',
            'Achevé' => 'label-success'
        ];

        return $statusMap[$this->statut] ?? 'label-default';
    }
}
