<?php

namespace App\Http\Controllers;

use App\Models\Indicateur;
use Illuminate\Http\Request;

class IndicateurController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    // Affichage de tous les indicateurs (avec pagination)
    public function index()
    {
        $indicateurs = Indicateur::orderBy('created_at', 'asc')->paginate(10);
        return view('indicateur', compact('indicateurs'));
    }

    // Affichage du formulaire de création
    public function create()
    {
        return view('indicateurs.create');
    }

    // Enregistrement d'un nouvel indicateur
    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:255',
            'unite' => 'required|string|max:255',
        ]);

        Indicateur::create([
            'libelle' => $request->libelle,
            'unite' => $request->unite,
        ]);

        return redirect()->route('indicateurs.index')->with('success', 'Indicateur ajouté.');
    }

    // Affichage du formulaire de modification
    public function edit($id)
    {
        $indicateur = Indicateur::findOrFail($id);
        return view('indicateurs.edit', compact('indicateur'));
    }

    // Mise à jour d'un indicateur existant
    public function update(Request $request, $id)
    {
        $request->validate([
            'libelle' => 'required|string|max:255',
            'unite' => 'required|string|max:255',
        ]);

        $indicateur = Indicateur::findOrFail($id);
        $indicateur->update([
            'libelle' => $request->libelle,
            'unite' => $request->unite,
        ]);

        return redirect()->route('indicateur.index')->with('success', 'Indicateur mis à jour.');
    }

    // Suppression d'un indicateur
    public function destroy($id)
    {
        $indicateur = Indicateur::findOrFail($id);
        $indicateur->delete();

        return redirect()->route('indicateur.index')->with('success', 'Indicateur supprimé.');
    }

    // Affichage d'un indicateur (si besoin)
    public function show($id)
    {
        $indicateur = Indicateur::findOrFail($id);
        return view('indicateurs.show', compact('indicateur'));
    }
}
