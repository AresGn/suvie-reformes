<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Reforme;

class ReformeComponent extends Component
{
    public $titre, $objectif, $budget, $date_debut, $date_prevue, $date_fin, $piecejustificatif, $typereforme;

    public function updated($fields) 
    {
        $this->validateOnly($fields, [
            'titre' => 'required',
            'objectif' => 'required',
            'budget' => 'required|numeric',
            'date_debut' => 'required|date',
            'date_prevue' => 'required|date',
            'date_fin' => 'required|date',
            'piecejustificatif' => 'required|file',
            'typereforme' => 'required',
        ]);  
    }

    public function StoreReforme()
    {
        $this->validate([
            'titre' => 'required',
            'objectif' => 'required',
            'budget' => 'required|numeric',
            'date_debut' => 'required|date',
            'date_prevue' => 'required|date',
            'date_fin' => 'required|date',
            'piecejustificatif' => 'required|file',
            'typereforme' => 'required',
        ]);

        $reforme = new Reforme();
        $reforme->titre = $this->titre;
        $reforme->objectifs = $this->objectif;
        $reforme->budget = $this->budget;
        $reforme->date_debut = $this->date_debut;
        $reforme->date_fin_prevue = $this->date_prevue;
        $reforme->date_fin = $this->date_fin;
        $reforme->pieces_justificatifs = $this->piecejustificatif;
        $reforme->type_reforme = $this->typereforme;
        $reforme->created_by = auth()->id();

        $reforme->save();

        session()->flash('message', 'Nouvelle réforme ajoutée avec succès.');

        $this->dispatchBrowserEvent('close-modal');

        // Réinitialiser les champs
        $this->reset();
    }

    public function render()
    {
        return view('livewire.reforme-component')->layout('layout.app');
    }
}