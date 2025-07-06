<?php

namespace App\Livewire;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use App\Models\menu; // Assure-toi que le modèle 'Menu' est bien écrit avec la majuscule 'M'

class Menutable extends Component
{
    public $menus = []; // Variable pour stocker les menus

    public function mount()
    {
        // Récupère tous les menus de la base de données
        $this->menus = menu::all(); // Assurez-vous que vous avez le bon modèle ici
    }

    public function render()
    {
        // Passe la variable $menus à la vue pour l'affichage
        return view('livewire.menutable', ['menus' => $this->menus]);
    }
}
