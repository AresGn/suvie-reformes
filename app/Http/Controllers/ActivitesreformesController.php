<?php

namespace App\Http\Controllers;

use App\Models\Activitesreformes;
use App\Models\Reforme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ActivitesreformesController extends Controller
{
    // Constructeur avec middleware d'authentification
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Afficher la liste des activités réformes
    public function index()
    {
        $activites = Activitesreformes::with('reforme')->whereNull('parent')->get();
        $reformes = Reforme::all();
        
        // Récupérer les structures avec toutes les informations nécessaires
        $structures = DB::table('reformes_structure as rs')
            ->join('structure as s', 'rs.structure_id', '=', 's.id')
            ->select('rs.id', 's.id as structure_id', 's.lib_court', 's.lib_long')
            ->get();
        
        return view('activitesreformes', compact('activites', 'reformes', 'structures'));
    }

    // Afficher le formulaire de création (redirection vers index car modal)
    public function create()
    {
        // Rediriger vers l'index car la création se fait via modal
        // Les données sont déjà chargées dans la méthode index()
        return redirect()->route('activites.index');
    }

    // Enregistrer une nouvelle activité de réforme
    public function store(Request $request)
    {
        try {
            // Déboguer les données reçues AVANT validation
            \Log::info('=== DÉBUT CRÉATION ACTIVITÉ ===');
            \Log::info('Données brutes reçues', ['data' => $request->all()]);
            \Log::info('Méthode HTTP', ['method' => $request->method()]);
            \Log::info('URL', ['url' => $request->url()]);
            \Log::info('Headers', ['headers' => $request->headers->all()]);

            $validatedData = $request->validate([
                'reforme_id' => 'required|exists:reformes,id',
                'libelle' => 'required|string|max:255',
                'date_debut' => 'required|date',
                'date_fin_prevue' => 'required|date|after:date_debut',
                'date_fin' => 'nullable|date',
                'poids' => 'required|integer|min:1|max:100',
                'structure_responsable' => 'required|integer|min:1|exists:reformes_structure,id',
            ], [
                // Messages personnalisés en français
                'reforme_id.required' => 'Veuillez sélectionner une réforme.',
                'reforme_id.exists' => 'La réforme sélectionnée n\'existe pas.',
                'libelle.required' => 'Le libellé de l\'activité est obligatoire.',
                'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
                'date_debut.required' => 'La date de début est obligatoire.',
                'date_debut.date' => 'La date de début doit être une date valide.',
                'date_fin_prevue.required' => 'La date de fin prévue est obligatoire.',
                'date_fin_prevue.date' => 'La date de fin prévue doit être une date valide.',
                'date_fin_prevue.after' => 'La date de fin prévue doit être postérieure à la date de début.',
                'date_fin.date' => 'La date de fin doit être une date valide.',
                'poids.required' => 'Le poids de l\'activité est obligatoire.',
                'poids.integer' => 'Le poids doit être un nombre entier.',
                'poids.min' => 'Le poids doit être au minimum de 1%.',
                'poids.max' => 'Le poids ne peut pas dépasser 100%.',
                'structure_responsable.required' => 'Veuillez sélectionner une structure responsable.',
                'structure_responsable.exists' => 'La structure responsable sélectionnée n\'existe pas.',
            ]);

            \Log::info('Validation réussie - Données après validation', ['validated_data' => $validatedData]);

            // Créer l'activité avec statut automatique par défaut (C)
            $activite = Activitesreformes::create([
                'reforme_id' => $validatedData['reforme_id'],
                'libelle' => $validatedData['libelle'],
                'date_debut' => $validatedData['date_debut'],
                'date_fin_prevue' => $validatedData['date_fin_prevue'],
                'date_fin' => $validatedData['date_fin'] ?? null,
                'poids' => $validatedData['poids'],
                'parent' => $validatedData['parent'] ?? null,
                'structure_responsable' => $validatedData['structure_responsable'],
                'created_by' => Auth::id() ?? 1, // Fallback si l'utilisateur n'est pas authentifié
            ]);

            // S'assurer que le statut est bien 'C' (Créé) par défaut
            if ($activite->statut !== 'C') {
                \Log::warning('Statut incorrect détecté après création', [
                    'statut_actuel' => $activite->statut,
                    'statut_attendu' => 'C'
                ]);
                $activite->updateStatut('C', Auth::id());
            }

            \Log::info('Activité créée avec succès', [
                'id' => $activite->id,
                'statut' => $activite->statut,
                'libelle' => $activite->libelle
            ]);

            // Vérifier que l'activité est bien en base
            $activiteVerif = Activitesreformes::find($activite->id);
            if ($activiteVerif) {
                \Log::info('Vérification base de données réussie - Activité trouvée', [
                    'id' => $activiteVerif->id,
                    'libelle' => $activiteVerif->libelle,
                    'statut' => $activiteVerif->statut,
                    'reforme_id' => $activiteVerif->reforme_id
                ]);
            } else {
                \Log::error('ERREUR: Activité non trouvée en base après création!');
            }

            \Log::info('=== FIN CRÉATION ACTIVITÉ RÉUSSIE ===');
            return redirect()->route('activites.index')->with('success', 'L\'activité a été créée avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation lors de la création d\'activité', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return redirect()->back()->withErrors($e->validator)->withInput()->with('error', 'Veuillez corriger les erreurs dans le formulaire.');

        } catch (\Exception $e) {
            \Log::error('Erreur technique lors de la création de l\'activité', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return redirect()->back()->withInput()->with('error', 'Une erreur technique est survenue lors de la création de l\'activité. Veuillez réessayer.');
        }
    }

    // Afficher le formulaire d'édition
    public function edit($id)
    {
        $activite = Activitesreformes::findOrFail($id);
        $reformes = Reforme::all();
        $activitesParentes = Activitesreformes::principales()->where('id', '!=', $id)->get();
        
        // Ajout de la récupération des structures
        $structures = \DB::table('reformes_structure as rs')
            ->join('structure as s', 'rs.structure_id', '=', 's.id')
            ->join('reformes as r', 'rs.reforme_id', '=', 'r.id')
            ->select('rs.id', 's.lib_court', 's.lib_long', 'r.titre as reforme_titre')
            ->get();
        
        return view('activites.edit', compact('activite', 'reformes', 'activitesParentes', 'structures'));
    }

    // Mettre à jour une activité de réforme
    public function update(Request $request, $id)
    {
        $request->validate([
            'reforme_id' => 'required|exists:reformes,id',
            'libelle' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin_prevue' => 'required|date|after:date_debut',
            'date_fin' => 'nullable|date',
            'poids' => 'required|integer|min:1|max:100',
            'parent' => 'nullable|exists:activites_reformes,id',
            'structure_responsable' => 'required|integer|min:1|exists:reformes_structure,id',
        ]);

        $activite = Activitesreformes::findOrFail($id);
        $activite->update([
            'reforme_id' => $request->reforme_id,
            'libelle' => $request->libelle,
            'date_debut' => $request->date_debut,
            'date_fin_prevue' => $request->date_fin_prevue,
            'date_fin' => $request->date_fin,
            'poids' => $request->poids,
            'parent' => $request->parent,
            'structure_responsable' => $request->structure_responsable,
            'updated_by' => Auth::id() ?? 1,
        ]);

        \Log::info('Activité mise à jour avec succès', [
            'id' => $activite->id,
            'statut_preserve' => $activite->statut,
            'libelle' => $activite->libelle
        ]);
        
        return redirect()->route('activites.index')->with('success', 'Activité mise à jour avec succès.');
    }

    // Supprimer une activité de réforme
    public function destroy($id)
    {
        $activite = Activitesreformes::findOrFail($id);
        
        // Vérifier s'il y a des sous-activités
        if ($activite->sousActivites()->count() > 0) {
            return redirect()->route('activites.index')
                ->with('error', 'Impossible de supprimer cette activité car elle a des sous-activités.');
        }
        
        $activite->delete();

        return redirect()->route('activites.index')->with('success', 'Activité supprimée avec succès.');
    }

    // Voir les détails d'une activité de réforme (redirection vers index car modal)
    public function show($id)
    {
        return redirect()->route('activites.index');
    }

    /**
     * Rediriger vers la page des sous-activités avec le modal d'ajout ouvert
     * 
     * @param int $activiteId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createSousActivite($activiteId)
    {
        try {
            // Débogage - Enregistrer l'ID de l'activité
            \Log::info('Tentative de création d\'une sous-activité', [
                'activiteId' => $activiteId
            ]);
            
            $activitePrincipale = Activitesreformes::findOrFail($activiteId);
            
            // Vérifier que c'est bien une activité principale
            if ($activitePrincipale->parent !== null) {
                \Log::warning('Tentative d\'ajout de sous-activité à une sous-activité', [
                    'activiteId' => $activiteId,
                    'parent' => $activitePrincipale->parent
                ]);
                
                return redirect()->route('activites.index')
                    ->with('error', "Vous ne pouvez ajouter des sous-activités qu'à une activité principale.");
            }
            
            // Débogage - Avant la redirection
            \Log::info('Redirection vers la page des sous-activités avec openAddModal', [
                'activiteId' => $activiteId,
                'route' => route('activites.sous-activites.index', $activiteId)
            ]);
            
            // Rediriger vers la page d'index des sous-activités avec une variable de session
            return redirect()->route('activites.sous-activites.index', $activiteId)
                ->with('openAddModal', true);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la redirection vers la page d\'ajout de sous-activité', [
                'activiteId' => $activiteId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('activites.index')
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    // Enregistrer une nouvelle sous-activité
    public function storeSousActivite(Request $request, $activiteId)
    {
        $activitePrincipale = Activitesreformes::findOrFail($activiteId);
        
        // Vérifier que c'est bien une activité principale
        if ($activitePrincipale->parent !== null) {
            return redirect()->route('activites.index')
                ->with('error', "Vous ne pouvez ajouter des sous-activités qu'à une activité principale.");
        }
        
        // Calculer le poids total des sous-activités existantes
        $totalPoids = $activitePrincipale->sousActivites()->sum('poids');
        $nouveauPoids = $request->poids;
        
        // Vérifier que le nouveau poids ne dépasse pas 100%
        if ($totalPoids + $nouveauPoids > 100) {
            return redirect()->route('activites.sous-activites.create', $activiteId)
                ->with('error', 'Le poids total des sous-activités ne peut pas dépasser 100%. Poids restant disponible: ' . (100 - $totalPoids) . '%')
                ->withInput();
        }
        
        $request->validate([
            'libelle' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin_prevue' => 'required|date|after:date_debut',
            'date_fin' => 'nullable|date',
            'poids' => 'required|integer|min:1|max:' . (100 - $totalPoids),
            'structure_responsable' => 'required|integer|min:1|exists:reformes_structure,id',
        ], [
            // Messages personnalisés en français pour sous-activités
            'libelle.required' => 'Le libellé de la sous-activité est obligatoire.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'date_debut.required' => 'La date de début est obligatoire.',
            'date_debut.date' => 'La date de début doit être une date valide.',
            'date_fin_prevue.required' => 'La date de fin prévue est obligatoire.',
            'date_fin_prevue.date' => 'La date de fin prévue doit être une date valide.',
            'date_fin_prevue.after' => 'La date de fin prévue doit être postérieure à la date de début.',
            'date_fin.date' => 'La date de fin doit être une date valide.',
            'poids.required' => 'Le poids de la sous-activité est obligatoire.',
            'poids.integer' => 'Le poids doit être un nombre entier.',
            'poids.min' => 'Le poids doit être au minimum de 1%.',
            'poids.max' => 'Le poids ne peut pas dépasser ' . (100 - $totalPoids) . '% (poids restant disponible).',
            'structure_responsable.required' => 'Veuillez sélectionner une structure responsable.',
            'structure_responsable.exists' => 'La structure responsable sélectionnée n\'existe pas.',
        ]);
        
        // Créer la sous-activité avec statut automatique par défaut (C)
        $sousActivite = Activitesreformes::create([
            'reforme_id' => $activitePrincipale->reforme_id,
            'libelle' => $request->libelle,
            'date_debut' => $request->date_debut,
            'date_fin_prevue' => $request->date_fin_prevue,
            'date_fin' => $request->date_fin,
            'poids' => $request->poids,
            'parent' => $activiteId,
            'structure_responsable' => $request->structure_responsable,
            'created_by' => Auth::id(),
        ]);

        // S'assurer que le statut est bien 'C' (Créé) par défaut
        if ($sousActivite->statut !== 'C') {
            \Log::warning('Statut incorrect détecté après création de sous-activité', [
                'statut_actuel' => $sousActivite->statut,
                'statut_attendu' => 'C',
                'parent_id' => $activiteId
            ]);
            $sousActivite->updateStatut('C', Auth::id());
        }

        \Log::info('Sous-activité créée avec succès', [
            'id' => $sousActivite->id,
            'statut' => $sousActivite->statut,
            'libelle' => $sousActivite->libelle,
            'parent_id' => $activiteId
        ]);
        
        return redirect()->route('activites.sous-activites.index', $activiteId)
            ->with('success', 'La sous-activité a été créée avec succès.');
    }

    /**
     * Afficher la liste des sous-activités d'une activité principale
     * 
     * @param int $activiteId
     * @return \Illuminate\View\View
     */
    public function indexSousActivites($activiteId)
    {
        try {
            // Débogage - Enregistrer l'ID de l'activité
            \Log::info('Tentative d\'accès à la page des sous-activités', [
                'activiteId' => $activiteId,
                'request_params' => request()->all(),
                'has_openAddModal' => request()->has('openAddModal')
            ]);
            
            // Récupérer l'activité principale avec les relations nécessaires
            // Temporairement sans la relation 'suivis' pour éviter l'erreur
            $activitePrincipale = Activitesreformes::with(['reforme', 'sousActivites.creator'])
                ->findOrFail($activiteId);
            
            // Débogage - Vérifier si l'activité est bien une activité principale
            \Log::info('Activité trouvée', [
                'id' => $activitePrincipale->id,
                'parent' => $activitePrincipale->parent,
                'is_principal' => $activitePrincipale->parent === null
            ]);
            
            // Vérifier que c'est bien une activité principale
            if ($activitePrincipale->parent !== null) {
                \Log::warning('Tentative d\'accès à une sous-activité comme activité principale', [
                    'activiteId' => $activiteId,
                    'parent' => $activitePrincipale->parent
                ]);
                
                return redirect()->route('activites.index')
                    ->with('error', 'Cette page n\'est accessible que pour les activités principales.');
            }
            
            // Récupérer les sous-activités
            $sousActivites = $activitePrincipale->sousActivites;
            
            // Calculer le poids total des sous-activités
            $totalPoids = $sousActivites->sum('poids');
            $poidsRestant = 100 - $totalPoids;
            
            // Récupérer les structures
            $structures = \DB::table('reformes_structure as rs')
                ->join('structure as s', 'rs.structure_id', '=', 's.id')
                ->join('reformes as r', 'rs.reforme_id', '=', 'r.id')
                ->select('rs.id', 's.lib_court', 's.lib_long', 'r.titre as reforme_titre')
                ->get();
            
            // Calculer les statistiques
            $statistiques = [
                'total' => $sousActivites->count(),
                'en_cours' => $sousActivites->where('statut', 'C')->count(),
                'en_pause' => $sousActivites->where('statut', 'P')->count(),
                'achevees' => $sousActivites->where('statut', 'A')->count(),
                'en_retard' => $sousActivites->filter(function($item) {
                    return $item->en_retard;
                })->count()
            ];
            
            // Journaliser les variables pour le débogage
            \Log::info('Données pour la vue des sous-activités', [
                'activitePrincipale' => $activitePrincipale->id,
                'sousActivites' => $sousActivites->count(),
                'structures' => $structures->count(),
                'totalPoids' => $totalPoids,
                'poidsRestant' => $poidsRestant,
                'openAddModal' => request()->has('openAddModal')
            ]);
            
            // Débogage - Avant de retourner la vue
            \Log::info('Préparation de la vue des sous-activités', [
                'activitePrincipale' => $activitePrincipale->id,
                'sousActivites' => isset($sousActivites) ? $sousActivites->count() : 0,
                'view_exists' => view()->exists('activites.sous-activites.index')
            ]);
            
            // Vérifier si on doit ouvrir le modal d'ajout
            $openAddModal = request()->has('openModal') || session()->has('openAddModal');

            return view('activites.sous-activites.index', compact(
                'activitePrincipale',
                'sousActivites',
                'totalPoids',
                'poidsRestant',
                'structures',
                'statistiques',
                'openAddModal'
            ));
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'affichage des sous-activités', [
                'activiteId' => $activiteId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('activites.index')
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    /**
     * Calculer les statistiques des sous-activités
     */
    private function calculerStatistiquesSousActivites($sousActivites)
    {
        $nombreTotal = $sousActivites->count();
        $nombreAchevees = $sousActivites->where('statut', 'A')->count();
        $nombreEnCours = $sousActivites->where('statut', 'P')->count();
        $nombreACommencer = $sousActivites->where('statut', 'C')->count();
        
        // Calculer le nombre d'activités en retard (date fin prévue dépassée et non achevée)
        $nombreEnRetard = $sousActivites->filter(function($activite) {
            return strtotime($activite->date_fin_prevue) < time() && $activite->statut != 'A';
        })->count();
        
        // Calculer l'avancement global pondéré par le poids
        $avancementGlobal = 0;
        $poidsTotal = $sousActivites->sum('poids');
        
        if ($poidsTotal > 0) {
            foreach ($sousActivites as $sa) {
                $avancementIndividuel = $sa->statut == 'A' ? 100 : ($sa->statut == 'P' ? 50 : 0);
                $avancementGlobal += ($avancementIndividuel * $sa->poids) / $poidsTotal;
            }
        }
        
        return [
            'nombre_total' => $nombreTotal,
            'nombre_achevees' => $nombreAchevees,
            'nombre_en_cours' => $nombreEnCours,
            'nombre_a_commencer' => $nombreACommencer,
            'nombre_en_retard' => $nombreEnRetard,
            'pourcentage_achevees' => $nombreTotal > 0 ? round(($nombreAchevees / $nombreTotal) * 100) : 0,
            'pourcentage_en_cours' => $nombreTotal > 0 ? round(($nombreEnCours / $nombreTotal) * 100) : 0,
            'pourcentage_a_commencer' => $nombreTotal > 0 ? round(($nombreACommencer / $nombreTotal) * 100) : 0,
            'pourcentage_en_retard' => $nombreTotal > 0 ? round(($nombreEnRetard / $nombreTotal) * 100) : 0,
            'avancement_global' => round($avancementGlobal),
        ];
    }

    /**
     * Afficher le formulaire d'édition d'une sous-activité
     * 
     * @param int $activiteId ID de l'activité principale
     * @param int $sousActiviteId ID de la sous-activité
     * @return \Illuminate\View\View
     */
    public function editSousActivite($activiteId, $sousActiviteId)
    {
        $activitePrincipale = Activitesreformes::findOrFail($activiteId);
        $sousActivite = Activitesreformes::findOrFail($sousActiviteId);
        
        // Vérifier que c'est bien une sous-activité de l'activité principale
        if ($sousActivite->parent != $activiteId) {
            return redirect()->route('activites.sous-activites.index', $activiteId)
                ->with('error', 'Cette sous-activité n\'appartient pas à l\'activité principale spécifiée.');
        }
        
        // Calculer le poids total des sous-activités existantes (sauf celle en cours d'édition)
        $totalPoids = $activitePrincipale->sousActivites()
            ->where('id', '!=', $sousActiviteId)
            ->sum('poids');
        $poidsRestant = 100 - $totalPoids;
        
        $reformes = Reforme::all();
        $structures = \DB::table('reformes_structure as rs')
            ->join('structure as s', 'rs.structure_id', '=', 's.id')
            ->join('reformes as r', 'rs.reforme_id', '=', 'r.id')
            ->select('rs.id', 's.lib_court', 's.lib_long', 'r.titre as reforme_titre')
            ->get();
        
        return view('activites.sous-activites.edit', compact('activitePrincipale', 'sousActivite', 'reformes', 'structures', 'poidsRestant'));
    }

    /**
     * Mettre à jour une sous-activité
     *
     * @param \Illuminate\Http\Request $request
     * @param int $activiteId ID de l'activité principale
     * @param int $sousActiviteId ID de la sous-activité
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSousActivite(Request $request, $activiteId, $sousActiviteId)
    {
        try {
            // Récupération des entités
            $activitePrincipale = Activitesreformes::findOrFail($activiteId);
            $sousActivite = Activitesreformes::findOrFail($sousActiviteId);

            // Vérification de la relation parent-enfant
            if ($sousActivite->parent != $activiteId) {
                return redirect()->route('activites.sous-activites.index', $activiteId)
                    ->with('error', 'Cette sous-activité n\'appartient pas à l\'activité principale spécifiée.');
            }

            // Validation des données (statut exclu car géré automatiquement)
            $request->validate([
                'libelle' => 'required|string|max:255',
                'date_debut' => 'required|date',
                'date_fin_prevue' => 'required|date|after_or_equal:date_debut',
                'date_fin' => 'nullable|date',
                'poids' => 'required|numeric|min:1|max:100',
                'structure_responsable' => 'required|exists:reformes_structure,id',
            ]);

            // Vérification du poids total
            $totalPoids = $activitePrincipale->sousActivites()
                ->where('id', '!=', $sousActiviteId)
                ->sum('poids');

            if ($totalPoids + $request->poids > 100) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Le poids total des sous-activités ne peut pas dépasser 100%. Poids restant disponible: ' . (100 - $totalPoids) . '%');
            }

            // Mise à jour de la sous-activité (statut préservé automatiquement)
            $sousActivite->update([
                'libelle' => $request->libelle,
                'date_debut' => $request->date_debut,
                'date_fin_prevue' => $request->date_fin_prevue,
                'date_fin' => $request->date_fin,
                'poids' => $request->poids,
                'structure_responsable' => $request->structure_responsable,
                'updated_by' => Auth::id(),
            ]);

            \Log::info('Sous-activité mise à jour avec succès', [
                'id' => $sousActivite->id,
                'statut_preserve' => $sousActivite->statut,
                'libelle' => $sousActivite->libelle,
                'parent_id' => $activiteId
            ]);

            // Redirection simple et directe
            return redirect()->route('activites.sous-activites.index', $activiteId)
                ->with('success', 'Sous-activité mise à jour avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Redirection avec erreurs de validation
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();

        } catch (\Exception $e) {
            // En cas d'erreur, redirection vers la liste des activités
            return redirect()->route('activites.index')
                ->with('error', 'Une erreur est survenue lors de la mise à jour de la sous-activité.');
        }
    }



    /**
     * Supprimer une sous-activité
     * 
     * @param int $activiteId ID de l'activité principale
     * @param int $sousActiviteId ID de la sous-activité
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroySousActivite($activiteId, $sousActiviteId)
    {
        $sousActivite = Activitesreformes::findOrFail($sousActiviteId);
        
        // Vérifier que c'est bien une sous-activité de l'activité principale
        if ($sousActivite->parent != $activiteId) {
            return redirect()->route('activites.sous-activites.index', $activiteId)
                ->with('error', 'Cette sous-activité n\'appartient pas à l\'activité principale spécifiée.');
        }
        
        // Vérifier s'il y a des suivis associés
        if ($sousActivite->suivis()->count() > 0) {
            // Option 1: Empêcher la suppression
            // return redirect()->route('activites.sous-activites.index', $activiteId)
            //     ->with('error', 'Impossible de supprimer cette sous-activité car elle a des suivis associés.');
            
            // Option 2: Supprimer les suivis associés
            $sousActivite->suivis()->delete();
        }
        
        // Supprimer la sous-activité
        $sousActivite->delete();
        
        return redirect()->route('activites.sous-activites.index', $activiteId)
            ->with('success', 'Sous-activité supprimée avec succès.');
    }

    /**
     * API - Récupérer le poids restant disponible pour une activité
     */
    public function getPoidsRestant($activiteId)
    {
        try {
            $activitePrincipale = Activitesreformes::findOrFail($activiteId);

            // Vérifier que c'est bien une activité principale
            if ($activitePrincipale->parent !== null) {
                return response()->json(['error' => 'Cette activité n\'est pas une activité principale'], 400);
            }

            // Calculer le poids total des sous-activités existantes
            $totalPoids = $activitePrincipale->sousActivites()->sum('poids');
            $poidsRestant = 100 - $totalPoids;

            return response()->json([
                'poids_restant' => max(0, $poidsRestant),
                'poids_utilise' => $totalPoids,
                'nombre_sous_activites' => $activitePrincipale->sousActivites()->count()
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors du calcul du poids restant'], 500);
        }
    }
}



























