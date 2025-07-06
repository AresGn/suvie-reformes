<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indicateur extends Model
{
    protected $table = 'indicateurs';

    protected $fillable = [
        'libelle',
        'unite'
    ];

    public function reformes() {
        return $this->belongsToMany(Reforme::class, 'reforme_indicateur');
    }
}
