<?php

namespace App\Console\Commands;

use App\Models\Session;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:cleanup 
                            {--days=90 : Nombre de jours à conserver}
                            {--inactive-hours=24 : Marquer comme inactives les sessions sans activité depuis X heures}
                            {--dry-run : Afficher ce qui serait supprimé sans effectuer la suppression}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoyer les anciennes sessions et marquer les sessions inactives';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysToKeep = $this->option('days');
        $inactiveHours = $this->option('inactive-hours');
        $dryRun = $this->option('dry-run');

        $this->info("🧹 Nettoyage des sessions");
        $this->info("📅 Conservation : {$daysToKeep} jours");
        $this->info("⏰ Inactivité : {$inactiveHours} heures");
        
        if ($dryRun) {
            $this->warn("🔍 Mode simulation (dry-run) - Aucune modification ne sera effectuée");
        }

        $this->newLine();

        // 1. Marquer les sessions inactives
        $this->markInactiveSessions($inactiveHours, $dryRun);

        // 2. Supprimer les anciennes sessions
        $this->deleteOldSessions($daysToKeep, $dryRun);

        // 3. Afficher les statistiques finales
        $this->showStatistics();

        $this->newLine();
        $this->info("✅ Nettoyage terminé !");
    }

    /**
     * Marquer les sessions inactives
     */
    protected function markInactiveSessions($inactiveHours, $dryRun)
    {
        $inactiveCount = Session::markInactiveSessions($inactiveHours);

        if ($inactiveCount > 0) {
            $this->info("🔄 Sessions marquées comme inactives : {$inactiveCount}");
        } else {
            $this->info("ℹ️  Aucune session inactive trouvée");
        }
    }

    /**
     * Supprimer les anciennes sessions
     */
    protected function deleteOldSessions($daysToKeep, $dryRun)
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        
        $query = Session::where('login_at', '<', $cutoffDate);
        $oldCount = $query->count();

        if ($oldCount > 0) {
            $this->info("🗑️  Sessions anciennes à supprimer : {$oldCount}");
            
            if (!$dryRun) {
                $deleted = $query->delete();
                $this->info("✅ Sessions supprimées : {$deleted}");
            }
        } else {
            $this->info("ℹ️  Aucune ancienne session à supprimer");
        }
    }

    /**
     * Afficher les statistiques
     */
    protected function showStatistics()
    {
        $stats = [
            'Total sessions' => Session::count(),
            'Sessions actives' => Session::where('status', 'active')->count(),
            'Sessions inactives' => Session::where('status', 'inactive')->count(),
            'Sessions aujourd\'hui' => Session::whereDate('login_at', Carbon::today())->count(),
            'Sessions cette semaine' => Session::where('login_at', '>=', Carbon::now()->startOfWeek())->count(),
            'Sessions ce mois' => Session::where('login_at', '>=', Carbon::now()->startOfMonth())->count(),
        ];

        $this->newLine();
        $this->info("📊 Statistiques des sessions :");
        
        foreach ($stats as $label => $value) {
            $this->line("   {$label}: {$value}");
        }
    }
}
