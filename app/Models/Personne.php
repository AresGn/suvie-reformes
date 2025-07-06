<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personne extends Model
{
    protected $table = 'personne';

    protected $fillable = ['nom', 'prenom', 'fonction', 'tel', 'email',];

    public function user()
    {
        return $this->hasOne(User::class, 'personne_id');
    }
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
