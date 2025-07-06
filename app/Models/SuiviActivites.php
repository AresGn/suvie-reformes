<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuiviActivites extends Model
{
    protected $table = 'suivi_activites';

    protected $fillable = [
        'activite_reforme_id',
        'suivi_date',
        'actions_fait',
        'actions_a_fait',
        'difficultes',
        'solutions',
        'observations',
        'created_by',
        'updated_by'
    ];

    // Relation avec l'activité de réforme
    public function activiteReforme()
    {
        return $this->belongsTo(Activitesreformes::class, 'activite_reforme_id');
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
}