<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InsererIndividus extends Command
{
    protected $signature = 'inserer:individus';
    protected $description = 'Insère des individus dans la base de données SQLite';

    public function handle()
    {
        $personnes = [
            ['nom' => 'Dupont', 'prenom' => 'Jean', 'fonction' => 'Directeur', 'tel' => '0123456789', 'email' => 'jean.dupont@example.com', 'pwd' => 'password1'],
            ['nom' => 'Durand', 'prenom' => 'Paul', 'fonction' => 'Chef de projet', 'tel' => '0678901234', 'email' => 'paul.durand@example.com', 'pwd' => 'password4'],
            ['nom' => 'Lemoine', 'prenom' => 'Sophie', 'fonction' => 'Analyste', 'tel' => '0765432198', 'email' => 'sophie.lemoine@example.com', 'pwd' => 'password5'],
        ];

        foreach ($personnes as $personne) {
            $personne_id = DB::table('personne')->insertGetId([
                'nom' => $personne['nom'],
                'prenom' => $personne['prenom'],
                'fonction' => $personne['fonction'],
                'tel' => $personne['tel'],
                'email' => $personne['email'],
            ]);

            DB::table('users')->insert([
                'personne_id' => $personne_id,
                'pwd' => Hash::make($personne['pwd']),
                'status' => 1
            ]);
        }

        $this->info('Individus insérés avec succès dans la base SQLite.');
    }
}
