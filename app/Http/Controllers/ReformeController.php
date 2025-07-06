<?php

namespace App\Http\Controllers;

use App\Models\Reforme;
use App\Models\Typereforme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Structure;
use App\Models\Indicateur;


class ReformeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Afficher la liste des réformes
    public function index()
    {
        $reformes = Reforme::with(['type', 'creator', 'updater'])->orderBy('id', 'asc')->get();
        $typereformes = Typereforme::all();
        $structures = Structure::all();
        $indicateurs = Indicateur::all();
        return view('reforme', compact('reformes', 'typereformes', 'structures', 'indicateurs'));
    }

    // Afficher le formulaire de création
    public function create()
    {
        $types = Typereforme::all();
        $structures = Structure::all();
        $indicateurs = Indicateur::all();
        return view('reforme.create', compact('types', 'structures', 'indicateurs'));
    }

    // Enregistrer une nouvelle réforme
    public function store(Request $request)
    {
        // Vérifier que l'utilisateur est authentifié
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour créer une réforme.');
        }

        $request->validate([
            'titre' => 'required|string|max:255',
            'objectifs' => 'required|string',
            'budget' => 'nullable|numeric',
            'date_debut' => 'nullable|date',
            'date_fin_prevue' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'pieces_justificatifs' => 'nullable|string',
            'type_reforme' => 'required|exists:type_reforme,id',
        ]);

        // Obtenir l'ID utilisateur avec fallback de sécurité
        $userId = Auth::id();
        if (!$userId) {
            // Fallback: utiliser l'ID 1 si aucun utilisateur n'est connecté (pour éviter l'erreur NOT NULL)
            // En production, ceci devrait rediriger vers login
            $userId = 1; // ID de l'utilisateur par défaut
            \Log::warning('Création de réforme sans utilisateur authentifié, utilisation de l\'ID par défaut');
        }

        Reforme::create([
            'titre' => $request->titre,
            'objectifs' => $request->objectifs,
            'budget' => $request->budget,
            'date_debut' => $request->date_debut,
            'date_fin_prevue' => $request->date_fin_prevue,
            'date_fin' => $request->date_fin,
            'pieces_justificatifs' => $request->pieces_justificatifs,
            'type_reforme' => $request->type_reforme,
            'created_by' => $userId,
        ]);

        return redirect()->route('reforme.index')->with('success', 'Réforme ajoutée avec succès.');
    }


    // Afficher le formulaire d’édition
    public function edit($id)
    {
        $reforme = Reforme::findOrFail($id);
        $types = Typereforme::all();
        $structures = Structure::all();
        $indicateurs = Indicateur::all();
        return view('reforme.edit', compact('reforme', 'types', 'structures', 'indicateurs'));
    }

    // Mettre à jour une réforme
    public function update(Request $request, $id)
    {
        // Vérifier que l'utilisateur est authentifié
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour modifier une réforme.');
        }

        $request->validate([
            'titre' => 'required|string|max:255',
            'objectifs' => 'required|string',
            'budget' => 'nullable|numeric',
            'date_debut' => 'nullable|date',
            'date_fin_prevue' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'pieces_justificatifs' => 'nullable|string',
            'type_reforme' => 'required|exists:type_reforme,id',
        ]);

        // Obtenir l'ID utilisateur avec fallback de sécurité
        $userId = Auth::id();
        if (!$userId) {
            // Fallback: utiliser l'ID 1 si aucun utilisateur n'est connecté
            $userId = 1; // ID de l'utilisateur par défaut
            \Log::warning('Modification de réforme sans utilisateur authentifié, utilisation de l\'ID par défaut');
        }

        $reforme = Reforme::findOrFail($id);
        $reforme->update([
            'titre' => $request->titre,
            'objectifs' => $request->objectifs,
            'budget' => $request->budget,
            'date_debut' => $request->date_debut,
            'date_fin_prevue' => $request->date_fin_prevue,
            'date_fin' => $request->date_fin,
            'pieces_justificatifs' => $request->pieces_justificatifs,
            'type_reforme' => $request->type_reforme,
            'updated_by' => $userId,
        ]);

        return redirect()->route('reforme.index')->with('success', 'Réforme mise à jour avec succès.');
    }

    // Supprimer une réforme
    public function destroy($id)
    {
        $reforme = Reforme::findOrFail($id);
        $reforme->delete();

        return redirect()->route('reforme.index')->with('success', 'Réforme supprimée avec succès.');
    }

    // Voir les détails d'une réforme
    public function show($id)
    {
        $reforme = Reforme::with(['type', 'creator', 'updater'])->findOrFail($id);
        return view('reforme.show', compact('reforme'));
    }
}
