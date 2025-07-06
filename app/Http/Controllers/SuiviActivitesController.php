<?php

namespace App\Http\Controllers;

use App\Models\Activitesreformes;
use App\Models\SuiviActivites;
use App\Models\Reforme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuiviActivitesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher la liste des sous-activités à suivre
     */
    public function index(Request $request)
    {
        // Récupérer uniquement les sous-activités (avec parent non null)
        $query = Activitesreformes::query()->sousActivites()
            ->with(['reforme', 'parentActivite', 'suivis', 'creator', 'dernierSuiviRelation']);
        
        // Filtrage par statut si spécifié
        if ($request->has('statut') && in_array($request->statut, ['C', 'P', 'A'])) {
            $query->where('statut', $request->statut);
        }
        
        // Filtrage par réforme si spécifié
        if ($request->has('reforme_id') && $request->reforme_id > 0) {
            $query->where('reforme_id', $request->reforme_id);
        }
        
        $sousActivites = $query->orderBy('date_fin_prevue', 'asc')->get();
        
        // Calculer le pourcentage d'avancement pour chaque activité
        foreach ($sousActivites as $activite) {
            $activite->avancement = $this->calculerAvancement($activite);
        }
        
        $reformes = \App\Models\Reforme::all();
        
        return view('suivi.index', compact('sousActivites', 'reformes'));
    }

    /**
     * Marquer une activité comme terminée (validation rapide)
     */
    public function validerActivite(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $activite = Activitesreformes::findOrFail($id);

            // Créer un suivi automatique
            SuiviActivites::create([
                'activite_reforme_id' => $activite->id,
                'suivi_date' => now(),
                'actions_fait' => 'Activité validée et terminée',
                'actions_a_fait' => 'Aucune action supplémentaire requise',
                'observations' => 'Validation rapide par ' . Auth::user()->name,
                'created_by' => Auth::id(),
            ]);

            // Terminer l'activité avec la nouvelle méthode sécurisée
            $activite->terminer(Auth::id());

            // Exécuter la cascade de validation automatique
            $cascadeInfo = $this->executeValidationCascade($activite);

            DB::commit();

            // Construire le message de réponse avec informations de cascade
            $message = 'Activité validée avec succès';
            if ($cascadeInfo['parent_validated']) {
                $message .= '. Activité parent automatiquement terminée';
            }
            if ($cascadeInfo['reform_validated']) {
                $message .= '. Réforme automatiquement terminée';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'cascade_info' => $cascadeInfo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur validation activité: ' . $e->getMessage(), [
                'activite_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Enregistrer un nouveau suivi pour une activité en cours
     */
    public function store(Request $request)
    {
        $request->validate([
            'activite_id' => 'required|exists:activites_reformes,id',
            'suivi_date' => 'required|date',
            'actions_fait' => 'required|string',
            'actions_a_fait' => 'required|string',
            'difficultes' => 'nullable|string',
            'solutions' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Créer le suivi
            SuiviActivites::create([
                'activite_reforme_id' => $request->activite_id,
                'suivi_date' => $request->suivi_date,
                'actions_fait' => $request->actions_fait,
                'actions_a_fait' => $request->actions_a_fait,
                'difficultes' => $request->difficultes,
                'solutions' => $request->solutions,
                'observations' => $request->observations,
                'created_by' => Auth::id(),
            ]);

            // Mettre à jour le statut de l'activité si nécessaire
            $activite = Activitesreformes::findOrFail($request->activite_id);
            $cascadeInfo = ['parent_validated' => false, 'reform_validated' => false];

            if ($request->has('terminer') && $request->terminer == 1) {
                // Terminer l'activité avec la nouvelle méthode sécurisée
                $activite->terminer(Auth::id());

                // Exécuter la cascade de validation automatique
                $cascadeInfo = $this->executeValidationCascade($activite);
            } else {
                // Démarrer l'activité si elle n'est pas encore en cours
                if ($activite->statut === 'C') {
                    $activite->demarrer(Auth::id());
                } else {
                    // Juste mettre à jour updated_by si déjà en cours
                    $activite->update(['updated_by' => Auth::id()]);
                }
            }

            DB::commit();

            // Construire le message de réponse avec informations de cascade
            $message = 'Suivi enregistré avec succès';
            if ($cascadeInfo['parent_validated']) {
                $message .= '. Activité parent automatiquement terminée';
            }
            if ($cascadeInfo['reform_validated']) {
                $message .= '. Réforme automatiquement terminée';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'cascade_info' => $cascadeInfo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Afficher l'historique des suivis d'une activité
     */
    public function historique($id)
    {
        $activite = Activitesreformes::with(['reforme', 'parentActivite'])->findOrFail($id);
        $suivis = SuiviActivites::where('activite_reforme_id', $id)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('suivi.historique', compact('activite', 'suivis'));
    }

    /**
     * Supprimer un suivi
     */
    public function destroy($id)
    {
        try {
            $suivi = SuiviActivites::findOrFail($id);
            $suivi->delete();
            
            return response()->json(['success' => true, 'message' => 'Suivi supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Calculer le pourcentage d'avancement d'une activité
     */
    private function calculerAvancement($activite)
    {
        if ($activite->statut == 'A') {
            return 100;
        } elseif ($activite->statut == 'C') {
            return 0;
        } else {
            // Calculer en fonction du nombre de suivis et de la date
            $totalDays = max(1, (strtotime($activite->date_fin_prevue) - strtotime($activite->date_debut)) / 86400);
            $elapsedDays = min($totalDays, max(0, (time() - strtotime($activite->date_debut)) / 86400));
            $timeProgress = min(90, round(($elapsedDays / $totalDays) * 100));
            
            // Ajuster en fonction du nombre de suivis
            $suiviCount = $activite->suivis->count();
            $suiviBonus = min(10, $suiviCount * 2); // 2% par suivi, max 10%
            
            return min(95, $timeProgress + $suiviBonus);
        }
    }

    /**
     * Exécuter la cascade de validation automatique
     * Vérifie et valide automatiquement les activités parent et réformes
     */
    private function executeValidationCascade($activite)
    {
        $cascadeInfo = [
            'parent_validated' => false,
            'reform_validated' => false,
            'cascade_errors' => []
        ];

        try {
            // 1. Vérifier et valider l'activité parent si nécessaire
            if ($activite->parent) {
                $cascadeInfo['parent_validated'] = $this->validateParentIfComplete($activite);
            }

            // 2. Vérifier et valider la réforme si nécessaire
            $cascadeInfo['reform_validated'] = $this->validateReformIfComplete($activite);

        } catch (\Exception $e) {
            // Log l'erreur mais ne pas faire échouer la transaction principale
            Log::error('Erreur cascade validation: ' . $e->getMessage(), [
                'activite_id' => $activite->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            $cascadeInfo['cascade_errors'][] = $e->getMessage();
        }

        return $cascadeInfo;
    }

    /**
     * Valider l'activité parent si toutes ses sous-activités sont terminées
     */
    private function validateParentIfComplete($activite)
    {
        try {
            $parentActivite = $activite->parentActivite;

            // Vérifier que le parent existe et n'est pas déjà terminé
            if (!$parentActivite || $parentActivite->statut === 'A') {
                return false;
            }

            // Vérifier que toutes les sous-activités du parent sont terminées
            $sousActivitesCount = Activitesreformes::where('parent', $parentActivite->id)->count();
            $sousActivitesTermineesCount = Activitesreformes::where('parent', $parentActivite->id)
                ->where('statut', 'A')
                ->count();

            // Si toutes les sous-activités sont terminées, valider le parent
            if ($sousActivitesCount > 0 && $sousActivitesCount === $sousActivitesTermineesCount) {

                // Créer un suivi automatique pour le parent
                SuiviActivites::create([
                    'activite_reforme_id' => $parentActivite->id,
                    'suivi_date' => now(),
                    'actions_fait' => 'Activité terminée automatiquement - toutes les sous-activités sont achevées',
                    'actions_a_fait' => 'Aucune action supplémentaire requise',
                    'observations' => 'Validation automatique en cascade par ' . Auth::user()->name,
                    'created_by' => Auth::id(),
                ]);

                // Terminer l'activité parent avec la nouvelle méthode sécurisée
                $parentActivite->terminer(Auth::id());

                Log::info('Activité parent validée automatiquement', [
                    'parent_id' => $parentActivite->id,
                    'triggered_by_activity' => $activite->id,
                    'user_id' => Auth::id()
                ]);

                // Récursion : vérifier si le parent du parent doit aussi être validé
                if ($parentActivite->parent) {
                    $this->validateParentIfComplete($parentActivite);
                }

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Erreur validation parent: ' . $e->getMessage(), [
                'activite_id' => $activite->id,
                'parent_id' => $activite->parent,
                'user_id' => Auth::id()
            ]);
            throw $e;
        }
    }

    /**
     * Valider la réforme si toutes ses activités sont terminées
     */
    private function validateReformIfComplete($activite)
    {
        try {
            $reforme = $activite->reforme;

            // Vérifier que la réforme existe et n'est pas déjà terminée
            if (!$reforme || $reforme->date_fin) {
                return false;
            }

            // Vérifier que toutes les activités principales de la réforme sont terminées
            $activitesCount = Activitesreformes::where('reforme_id', $reforme->id)
                ->whereNull('parent') // Seulement les activités principales
                ->count();

            $activitesTermineesCount = Activitesreformes::where('reforme_id', $reforme->id)
                ->whereNull('parent')
                ->where('statut', 'A')
                ->count();

            // Si toutes les activités principales sont terminées, valider la réforme
            if ($activitesCount > 0 && $activitesCount === $activitesTermineesCount) {

                // Mettre à jour la réforme
                $reforme->update([
                    'date_fin' => now(),
                    'updated_by' => Auth::id(),
                ]);

                Log::info('Réforme validée automatiquement', [
                    'reforme_id' => $reforme->id,
                    'triggered_by_activity' => $activite->id,
                    'user_id' => Auth::id()
                ]);

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Erreur validation réforme: ' . $e->getMessage(), [
                'activite_id' => $activite->id,
                'reforme_id' => $activite->reforme_id,
                'user_id' => Auth::id()
            ]);
            throw $e;
        }
    }
}
