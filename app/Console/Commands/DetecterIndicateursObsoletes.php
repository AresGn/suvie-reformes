<?php

namespace App\Console\Commands;

use App\Services\SuiviIndicateurService;
use Illuminate\Console\Command;

class DetecterIndicateursObsoletes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indicateurs:detecter-obsoletes {--seuil=30 : Nombre de jours sans mise à jour pour considérer un indicateur comme obsolète}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Détecter les indicateurs qui n\'ont pas été mis à jour depuis un certain nombre de jours et envoyer des notifications';

    protected $suiviIndicateurService;

    /**
     * Create a new command instance.
     */
    public function __construct(SuiviIndicateurService $suiviIndicateurService)
    {
        parent::__construct();
        $this->suiviIndicateurService = $suiviIndicateurService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $seuil = (int) $this->option('seuil');
        
        $this->info("Détection des indicateurs obsolètes (seuil: {$seuil} jours)...");
        
        try {
            $this->suiviIndicateurService->detecterIndicateursObsoletes($seuil);
            $this->info('Détection terminée avec succès. Les notifications ont été envoyées si nécessaire.');
        } catch (\Exception $e) {
            $this->error('Erreur lors de la détection des indicateurs obsolètes: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
