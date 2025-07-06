<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Livewire;
use App\Models\Menu;

class MenuComponent extends Component
{
    public $menus = []; // Variable pour stocker les menus

    public function mount()
    {
        // Récupère tous les menus de la base de données
        $this->menus = Menu::all();
    }

    public function render()
    {
        return view('livewire.menu-component');
    }
}
