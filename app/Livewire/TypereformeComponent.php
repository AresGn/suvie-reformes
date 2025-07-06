<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Typereforme;

class TypereformeComponent extends Component
{
    public $lib, $type_id;
    public $types;
    public $isOpen = false;

    protected $rules = [
        'lib' => 'required|string|max:255',
    ];

    
    protected $listeners = [
        'closeModal' => 'resetForm'
    ];

    public function mount()
    {
        $this->loadTypes();
    }

    public function loadTypes()
    {
        $this->types = Typereforme::all();
    }

    public function render()
    {
        return view('livewire.typereforme-component')->layout('layout.app');
    }

    public function resetForm()
    {
        $this->reset(['lib', 'type_id']);
        $this->resetValidation();
    }

    public function showForm()
    {
        $this->resetForm();
        $this->isOpen = true;
        
    }

    public function save()
    {
        $this->validate();

        Typereforme::updateOrCreate(
            ['id' => $this->type_id],
            ['lib' => $this->lib]
        );

        session()->flash('message', $this->type_id ? 'Modifié avec succès' : 'Ajouté avec succès');
        $this->resetForm();
        $this->isOpen = false;
        // Dispatch l'événement pour fermer le modal programmatiquement
        $this->dispatchBrowserEvent('close-modal');
        $this->loadTypes();
    }

    public function edit($id)
    {
        $type = Typereforme::findOrFail($id);
        $this->type_id = $type->id;
        $this->lib = $type->lib;
        $this->isOpen = true;
        // Le modal est maintenant géré par Bootstrap via data-bs-* attributes
    }

    public function delete($id)
    {
        Typereforme::findOrFail($id)->delete();
        session()->flash('message', 'Supprimé avec succès');
        $this->loadTypes();
    }
}