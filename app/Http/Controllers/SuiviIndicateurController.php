<?php

namespace App\Http\Controllers;

use App\Services\SuiviIndicateurService;
use App\Services\NotificationService;
use App\Models\ReformeIndicateur;
use App\Models\EvolutionIndicateur;
use App\Models\Reforme;
use App\Models\Indicateur;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class SuiviIndicateurController extends Controller
{
    protected SuiviIndicateurService $suiviService;
    protected NotificationService $notificationService;

    public function __construct(SuiviIndicateurService $suiviService, NotificationService $notificationService)
    {
        $this->suiviService = $suiviService;
        $this->notificationService = $notificationService;
    }

    /**
     * Afficher le tableau de bord général du suivi des indicateurs
     */
    public function index(): View
    {
        $rapport = $this->suiviService->getRapportSuiviGlobal();
        $alertes = $this->suiviService->getAlertesSuivi();
        
        return view('suivi-indicateurs.index', compact('rapport', 'alertes'));
    }

    /**
     * Afficher le tableau de bord d'une réforme spécifique
     */
    public function tableauBordReforme(int $reformeId): View
    {
        $donnees = $this->suiviService->getTableauBordReforme($reformeId);
        $indicateursDisponibles = Indicateur::whereDoesntHave('reformeIndicateurs', function($query) use ($reformeId) {
            $query->where('reforme_id', $reformeId);
        })->get();

        return view('suivi-indicateurs.tableau-bord', array_merge($donnees, [
            'indicateurs_disponibles' => $indicateursDisponibles
        ]));
    }

    /**
     * Associer un indicateur à une réforme
     */
    public function associerIndicateur(Request $request, int $reformeId): RedirectResponse
    {
        $request->validate([
            'indicateur_id' => 'required|exists:indicateurs,id'
        ]);

        try {
            $reformeIndicateur = $this->suiviService->associerIndicateurReforme(
                $reformeId, 
                $request->indicateur_id
            );

            // Notification
            $reforme = Reforme::find($reformeId);
            $indicateur = Indicateur::find($request->indicateur_id);
            
            $this->notificationService->createNotificationForRole(
                'gestionnaire',
                "Nouvel indicateur '{$indicateur->libelle}' associé à la réforme '{$reforme->titre}'",
                route('suivi-indicateurs.tableau-bord', $reformeId)
            );

            return redirect()->back()->with('success', 'Indicateur associé avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'association : ' . $e->getMessage());
        }
    }

    /**
     * Dissocier un indicateur d'une réforme
     */
    public function dissocierIndicateur(int $reformeId, int $indicateurId): RedirectResponse
    {
        try {
            $success = $this->suiviService->dissocierIndicateurReforme($reformeId, $indicateurId);
            
            if ($success) {
                return redirect()->back()->with('success', 'Indicateur dissocié avec succès');
            } else {
                return redirect()->back()->with('error', 'Indicateur non trouvé');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la dissociation : ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire d'ajout d'évolution
     */
    public function creerEvolution(int $reformeIndicateurId): View
    {
        $reformeIndicateur = ReformeIndicateur::with(['reforme', 'indicateur', 'evolutions'])
            ->findOrFail($reformeIndicateurId);

        return view('suivi-indicateurs.creer-evolution', compact('reformeIndicateur'));
    }

    /**
     * Enregistrer une nouvelle évolution
     */
    public function stockerEvolution(Request $request, int $reformeIndicateurId): RedirectResponse
    {
        $request->validate([
            'valeur' => 'required|numeric',
            'date_evolution' => 'required|date|before_or_equal:today'
        ]);

        try {
            $evolution = $this->suiviService->ajouterEvolution(
                $reformeIndicateurId,
                $request->valeur,
                $request->date_evolution
            );

            // Notification pour les gestionnaires
            $reformeIndicateur = ReformeIndicateur::with(['reforme', 'indicateur'])->find($reformeIndicateurId);
            
            $this->notificationService->createNotificationForRole(
                'gestionnaire',
                "Nouvelle évolution pour l'indicateur '{$reformeIndicateur->indicateur->libelle}' de la réforme '{$reformeIndicateur->reforme->titre}'",
                route('suivi-indicateurs.tableau-bord', $reformeIndicateur->reforme_id)
            );

            return redirect()->route('suivi-indicateurs.tableau-bord', $reformeIndicateur->reforme_id)
                ->with('success', 'Évolution ajoutée avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'ajout : ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire de modification d'évolution
     */
    public function modifierEvolution(int $reformeIndicateurId, string $date): View
    {
        $reformeIndicateur = ReformeIndicateur::with(['reforme', 'indicateur'])->findOrFail($reformeIndicateurId);
        $evolution = EvolutionIndicateur::where('reforme_indicateur_id', $reformeIndicateurId)
            ->where('date_evolution', $date)
            ->firstOrFail();

        return view('suivi-indicateurs.modifier-evolution', compact('reformeIndicateur', 'evolution'));
    }

    /**
     * Mettre à jour une évolution
     */
    public function mettreAJourEvolution(Request $request, int $reformeIndicateurId, string $date): RedirectResponse
    {
        $request->validate([
            'valeur' => 'required|numeric'
        ]);

        try {
            $evolution = $this->suiviService->mettreAJourEvolution(
                $reformeIndicateurId,
                $date,
                $request->valeur
            );

            if ($evolution) {
                return redirect()->route('suivi-indicateurs.tableau-bord', $evolution->reformeIndicateur->reforme_id)
                    ->with('success', 'Évolution mise à jour avec succès');
            } else {
                return redirect()->back()->with('error', 'Évolution non trouvée');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une évolution
     */
    public function supprimerEvolution(int $reformeIndicateurId, string $date): RedirectResponse
    {
        try {
            $reformeIndicateur = ReformeIndicateur::findOrFail($reformeIndicateurId);
            $success = $this->suiviService->supprimerEvolution($reformeIndicateurId, $date);
            
            if ($success) {
                return redirect()->route('suivi-indicateurs.tableau-bord', $reformeIndicateur->reforme_id)
                    ->with('success', 'Évolution supprimée avec succès');
            } else {
                return redirect()->back()->with('error', 'Évolution non trouvée');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * API : Obtenir les données pour un graphique
     */
    public function apiDonneesGraphique(int $reformeIndicateurId, Request $request): JsonResponse
    {
        $nombreMois = $request->get('mois', 12);
        
        try {
            $donnees = $this->suiviService->getDonneesGraphique($reformeIndicateurId, $nombreMois);
            return response()->json($donnees);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API : Obtenir les statistiques d'une réforme
     */
    public function apiStatistiquesReforme(int $reformeId): JsonResponse
    {
        try {
            $statistiques = $this->suiviService->getStatistiquesReforme($reformeId);
            return response()->json($statistiques);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Afficher la page d'import en lot
     */
    public function importLot(): View
    {
        $reformeIndicateurs = ReformeIndicateur::with(['reforme', 'indicateur'])->get();
        return view('suivi-indicateurs.import-lot', compact('reformeIndicateurs'));
    }

    /**
     * Traiter l'import en lot
     */
    public function traiterImportLot(Request $request): RedirectResponse
    {
        $request->validate([
            'fichier' => 'required|file|mimes:csv,txt',
        ]);

        try {
            $fichier = $request->file('fichier');
            $donnees = $this->parseCSV($fichier);
            
            $resultats = $this->suiviService->importerEvolutionsLot($donnees);
            
            $message = "Import terminé : {$resultats['succes']} succès, {$resultats['erreurs']} erreurs";
            
            if ($resultats['erreurs'] > 0) {
                return redirect()->back()
                    ->with('warning', $message)
                    ->with('erreurs_import', $resultats['messages']);
            } else {
                return redirect()->back()->with('success', $message);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Afficher les alertes de suivi
     */
    public function alertes(): View
    {
        $alertes = $this->suiviService->getAlertesSuivi();
        return view('suivi-indicateurs.alertes', compact('alertes'));
    }

    /**
     * Parser un fichier CSV
     */
    private function parseCSV($fichier): array
    {
        $donnees = [];
        $handle = fopen($fichier->getPathname(), 'r');
        
        // Ignorer la première ligne (en-têtes)
        fgetcsv($handle);
        
        while (($ligne = fgetcsv($handle)) !== false) {
            if (count($ligne) >= 3) {
                $donnees[] = [
                    'reforme_indicateur_id' => (int) $ligne[0],
                    'date' => $ligne[1],
                    'valeur' => (float) $ligne[2]
                ];
            }
        }
        
        fclose($handle);
        return $donnees;
    }

    /**
     * Exporter les données d'une réforme en CSV
     */
    public function exporterCSV(int $reformeId)
    {
        $donnees = $this->suiviService->getTableauBordReforme($reformeId);
        $reforme = $donnees['reforme'];
        
        $filename = "indicateurs_reforme_{$reformeId}_" . date('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($donnees) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, ['Indicateur', 'Unité', 'Valeur Actuelle', 'Valeur Initiale', 'Évolution %', 'Tendance', 'Dernière MAJ']);
            
            foreach ($donnees['indicateurs'] as $indicateur) {
                fputcsv($file, [
                    $indicateur['indicateur']->libelle,
                    $indicateur['indicateur']->unite,
                    $indicateur['valeur_actuelle'],
                    $indicateur['valeur_initiale'],
                    $indicateur['evolution_pourcentage'],
                    $indicateur['tendance'],
                    $indicateur['derniere_evolution']?->date_formatee ?? 'N/A'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
