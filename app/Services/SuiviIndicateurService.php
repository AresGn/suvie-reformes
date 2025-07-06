<?php

namespace App\Services;

use App\Models\ReformeIndicateur;
use App\Models\EvolutionIndicateur;
use App\Models\Reforme;
use App\Models\Indicateur;
use App\Events\EvolutionIndicateurCreated;
use App\Events\IndicateurSeuilDepasse;
use App\Events\IndicateurObsolete;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SuiviIndicateurService
{
    /**
     * Associer un indicateur à une réforme
     */
    public function associerIndicateurReforme(int $reformeId, int $indicateurId): ReformeIndicateur
    {
        // Vérifier si l'association existe déjà
        $existant = ReformeIndicateur::where('reforme_id', $reformeId)
            ->where('indicateur_id', $indicateurId)
            ->first();

        if ($existant) {
            return $existant;
        }

        return ReformeIndicateur::create([
            'reforme_id' => $reformeId,
            'indicateur_id' => $indicateurId
        ]);
    }

    /**
     * Dissocier un indicateur d'une réforme
     */
    public function dissocierIndicateurReforme(int $reformeId, int $indicateurId): bool
    {
        $reformeIndicateur = ReformeIndicateur::where('reforme_id', $reformeId)
            ->where('indicateur_id', $indicateurId)
            ->first();

        if (!$reformeIndicateur) {
            return false;
        }

        // Supprimer toutes les évolutions associées
        $reformeIndicateur->evolutions()->delete();
        
        // Supprimer l'association
        return $reformeIndicateur->delete();
    }

    /**
     * Ajouter une évolution à un indicateur de réforme
     */
    public function ajouterEvolution(int $reformeIndicateurId, float $valeur, string $date = null): EvolutionIndicateur
    {
        $date = $date ?: Carbon::now()->format('Y-m-d');

        $evolution = EvolutionIndicateur::creerEvolution($reformeIndicateurId, $valeur, $date);

        // Déclencher l'événement de création d'évolution
        event(new EvolutionIndicateurCreated($evolution));

        // Vérifier les seuils et déclencher des alertes si nécessaire
        $this->verifierSeuils($evolution);

        return $evolution;
    }

    /**
     * Mettre à jour une évolution existante
     */
    public function mettreAJourEvolution(int $reformeIndicateurId, string $date, float $nouvelleValeur): ?EvolutionIndicateur
    {
        $evolution = EvolutionIndicateur::where('reforme_indicateur_id', $reformeIndicateurId)
            ->where('date_evolution', $date)
            ->first();

        if (!$evolution) {
            return null;
        }

        $evolution->update(['valeur' => $nouvelleValeur]);
        return $evolution;
    }

    /**
     * Supprimer une évolution
     */
    public function supprimerEvolution(int $reformeIndicateurId, string $date): bool
    {
        return EvolutionIndicateur::where('reforme_indicateur_id', $reformeIndicateurId)
            ->where('date_evolution', $date)
            ->delete() > 0;
    }

    /**
     * Obtenir le tableau de bord des indicateurs pour une réforme
     */
    public function getTableauBordReforme(int $reformeId): array
    {
        $reforme = Reforme::with(['reformeIndicateurs.indicateur', 'reformeIndicateurs.evolutions'])
            ->findOrFail($reformeId);

        $indicateurs = [];

        foreach ($reforme->reformeIndicateurs as $reformeIndicateur) {
            $indicateurs[] = [
                'id' => $reformeIndicateur->id,
                'indicateur' => $reformeIndicateur->indicateur,
                'valeur_actuelle' => $reformeIndicateur->valeur_actuelle,
                'valeur_initiale' => $reformeIndicateur->valeur_initiale,
                'evolution_pourcentage' => $reformeIndicateur->evolution_pourcentage,
                'tendance' => $reformeIndicateur->tendance,
                'icone_tendance' => $reformeIndicateur->icone_tendance,
                'nombre_evolutions' => $reformeIndicateur->nombre_evolutions,
                'derniere_evolution' => $reformeIndicateur->derniereEvolution(),
                'has_data_recente' => $reformeIndicateur->hasDataRecente()
            ];
        }

        return [
            'reforme' => $reforme,
            'indicateurs' => $indicateurs,
            'statistiques' => $this->getStatistiquesReforme($reformeId)
        ];
    }

    /**
     * Obtenir les statistiques générales d'une réforme
     */
    public function getStatistiquesReforme(int $reformeId): array
    {
        $reformeIndicateurs = ReformeIndicateur::forReforme($reformeId)->get();
        
        $totalIndicateurs = $reformeIndicateurs->count();
        $indicateursAvecDonnees = $reformeIndicateurs->filter(function($ri) {
            return $ri->nombre_evolutions > 0;
        })->count();
        
        $indicateursRecents = $reformeIndicateurs->filter(function($ri) {
            return $ri->hasDataRecente();
        })->count();

        $tendances = $reformeIndicateurs->groupBy('tendance');

        return [
            'total_indicateurs' => $totalIndicateurs,
            'indicateurs_avec_donnees' => $indicateursAvecDonnees,
            'indicateurs_recents' => $indicateursRecents,
            'pourcentage_completion' => $totalIndicateurs > 0 ? round(($indicateursAvecDonnees / $totalIndicateurs) * 100, 1) : 0,
            'tendances' => [
                'hausse' => $tendances->get('hausse', collect())->count(),
                'baisse' => $tendances->get('baisse', collect())->count(),
                'stable' => $tendances->get('stable', collect())->count()
            ]
        ];
    }

    /**
     * Obtenir les données pour un graphique d'évolution
     */
    public function getDonneesGraphique(int $reformeIndicateurId, int $nombreMois = 12): array
    {
        $reformeIndicateur = ReformeIndicateur::findOrFail($reformeIndicateurId);
        $evolutions = $reformeIndicateur->evolutionsDerniersMois($nombreMois);

        $labels = [];
        $donnees = [];

        foreach ($evolutions as $evolution) {
            $labels[] = $evolution->date_formatee;
            $donnees[] = (float) $evolution->valeur;
        }

        return [
            'labels' => $labels,
            'donnees' => $donnees,
            'indicateur' => $reformeIndicateur->indicateur,
            'unite' => $reformeIndicateur->indicateur->unite ?? ''
        ];
    }

    /**
     * Obtenir les indicateurs nécessitant une mise à jour
     */
    public function getIndicateursAMettreAJour(int $joursLimite = 30): Collection
    {
        return ReformeIndicateur::with(['reforme', 'indicateur'])
            ->whereDoesntHave('evolutions', function($query) use ($joursLimite) {
                $dateLimit = Carbon::now()->subDays($joursLimite);
                $query->where('date_evolution', '>=', $dateLimit);
            })
            ->orWhereHas('evolutions', function($query) use ($joursLimite) {
                $dateLimit = Carbon::now()->subDays($joursLimite);
                $query->where('date_evolution', '<', $dateLimit);
            })
            ->get();
    }

    /**
     * Importer des évolutions en lot
     */
    public function importerEvolutionsLot(array $donnees): array
    {
        $resultats = [
            'succes' => 0,
            'erreurs' => 0,
            'messages' => []
        ];

        DB::beginTransaction();

        try {
            foreach ($donnees as $ligne) {
                try {
                    $this->ajouterEvolution(
                        $ligne['reforme_indicateur_id'],
                        $ligne['valeur'],
                        $ligne['date'] ?? null
                    );
                    $resultats['succes']++;
                } catch (\Exception $e) {
                    $resultats['erreurs']++;
                    $resultats['messages'][] = "Erreur ligne {$ligne['reforme_indicateur_id']}: " . $e->getMessage();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $resultats;
    }

    /**
     * Obtenir un rapport de suivi pour toutes les réformes
     */
    public function getRapportSuiviGlobal(): array
    {
        $reformes = Reforme::with(['reformeIndicateurs.indicateur'])->get();
        $rapport = [];

        foreach ($reformes as $reforme) {
            $statistiques = $this->getStatistiquesReforme($reforme->id);
            
            $rapport[] = [
                'reforme' => $reforme,
                'statistiques' => $statistiques,
                'score_suivi' => $this->calculerScoreSuivi($statistiques)
            ];
        }

        // Trier par score de suivi décroissant
        usort($rapport, function($a, $b) {
            return $b['score_suivi'] <=> $a['score_suivi'];
        });

        return $rapport;
    }

    /**
     * Calculer un score de suivi (0-100)
     */
    private function calculerScoreSuivi(array $statistiques): int
    {
        $score = 0;
        
        // 40% pour le pourcentage de completion
        $score += $statistiques['pourcentage_completion'] * 0.4;
        
        // 30% pour les données récentes
        if ($statistiques['total_indicateurs'] > 0) {
            $pourcentageRecent = ($statistiques['indicateurs_recents'] / $statistiques['total_indicateurs']) * 100;
            $score += $pourcentageRecent * 0.3;
        }
        
        // 30% pour la présence d'indicateurs
        if ($statistiques['total_indicateurs'] > 0) {
            $score += 30;
        }

        return min(100, round($score));
    }

    /**
     * Obtenir les alertes de suivi
     */
    public function getAlertesSuivi(): array
    {
        $alertes = [];

        // Indicateurs sans données récentes
        $indicateursSansMAJ = $this->getIndicateursAMettreAJour(30);
        foreach ($indicateursSansMAJ as $ri) {
            $alertes[] = [
                'type' => 'warning',
                'message' => "L'indicateur '{$ri->indicateur->libelle}' de la réforme '{$ri->reforme->titre}' n'a pas été mis à jour depuis 30 jours",
                'reforme_id' => $ri->reforme_id,
                'indicateur_id' => $ri->indicateur_id
            ];
        }

        // Réformes sans indicateurs
        $reformesSansIndicateurs = Reforme::whereDoesntHave('reformeIndicateurs')->get();
        foreach ($reformesSansIndicateurs as $reforme) {
            $alertes[] = [
                'type' => 'info',
                'message' => "La réforme '{$reforme->titre}' n'a aucun indicateur de suivi",
                'reforme_id' => $reforme->id
            ];
        }

        return $alertes;
    }

    /**
     * Vérifier les seuils d'alerte pour une évolution
     */
    protected function verifierSeuils(EvolutionIndicateur $evolution): void
    {
        $reformeIndicateur = $evolution->reformeIndicateur;
        $indicateur = $reformeIndicateur->indicateur;

        // Seuils configurables (peuvent être stockés en base de données)
        $seuilCritique = 20; // Variation de plus de 20% considérée comme critique
        $seuilAmelioration = 15; // Amélioration de plus de 15% considérée comme excellente
        $seuilAlerte = 10; // Variation de plus de 10% génère une alerte

        $variationPrecedente = $evolution->variation_precedente;

        if ($variationPrecedente !== null) {
            // Vérifier les seuils critiques (baisse importante)
            if ($variationPrecedente <= -$seuilCritique) {
                event(new IndicateurSeuilDepasse($evolution, 'critique', $seuilCritique));
            }
            // Vérifier les améliorations importantes
            elseif ($variationPrecedente >= $seuilAmelioration) {
                event(new IndicateurSeuilDepasse($evolution, 'amelioration', $seuilAmelioration));
            }
            // Vérifier les alertes générales
            elseif (abs($variationPrecedente) >= $seuilAlerte) {
                event(new IndicateurSeuilDepasse($evolution, 'alerte', $seuilAlerte));
            }
        }
    }

    /**
     * Détecter et notifier les indicateurs obsolètes
     */
    public function detecterIndicateursObsoletes(int $seuilJours = 30): void
    {
        $dateLimit = Carbon::now()->subDays($seuilJours);

        $indicateursObsoletes = ReformeIndicateur::whereDoesntHave('evolutions', function($query) use ($dateLimit) {
            $query->where('date_evolution', '>=', $dateLimit);
        })
        ->orWhereHas('evolutions', function($query) use ($dateLimit) {
            $query->where('date_evolution', '<', $dateLimit)
                  ->whereRaw('date_evolution = (SELECT MAX(date_evolution) FROM evolution_indicateur WHERE reforme_indicateur_id = reformes_indicateurs.id)');
        })
        ->with(['reforme', 'indicateur'])
        ->get();

        foreach ($indicateursObsoletes as $reformeIndicateur) {
            $derniereEvolution = $reformeIndicateur->derniereEvolution();
            $joursDepuisDerniereEvolution = $derniereEvolution
                ? Carbon::parse($derniereEvolution->date_evolution)->diffInDays(Carbon::now())
                : 999; // Valeur élevée si aucune évolution

            if ($joursDepuisDerniereEvolution >= $seuilJours) {
                event(new IndicateurObsolete($reformeIndicateur, $joursDepuisDerniereEvolution));
            }
        }
    }

    /**
     * Exporter les données d'une réforme en CSV
     */
    public function exporterCSV(Reforme $reforme): string
    {
        $reformeIndicateurs = $reforme->reformeIndicateurs()->with(['indicateur', 'evolutions'])->get();

        $csvData = [];
        $csvData[] = ['Indicateur', 'Date', 'Valeur', 'Variation (%)', 'Type Variation'];

        foreach ($reformeIndicateurs as $reformeIndicateur) {
            foreach ($reformeIndicateur->evolutions as $evolution) {
                $csvData[] = [
                    $reformeIndicateur->indicateur->nom,
                    $evolution->date_evolution,
                    $evolution->valeur,
                    $evolution->variation_precedente ?? 0,
                    $evolution->type_variation ?? 'stable'
                ];
            }
        }

        $filename = 'indicateurs_' . $reforme->libelle . '_' . Carbon::now()->format('Y-m-d') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        // Créer le répertoire s'il n'existe pas
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');
        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        return $filepath;
    }
}
