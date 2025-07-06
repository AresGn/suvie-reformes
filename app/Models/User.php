<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'personne_id',
        'pwd',
        'status',
    ];

    protected $hidden = [
        'pwd',
    ];

    // Utiliser le bon champ de mot de passe
    public function getAuthPassword()
    {
        return $this->pwd;
    }

    public function personne()
    {
        return $this->belongsTo(Personne::class, 'personne_id');
    }

    /**
     * Retourne la personne associée ou lance une exception si absente.
     */
    public function personneOrFail()
    {
        if (!$this->personne) {
            throw new \Exception('Personne liée introuvable pour l\'utilisateur ID=' . $this->id);
        }
        return $this->personne;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role', 'id_user', 'role_id');
    }

    // Les autres relations sessions...
}
