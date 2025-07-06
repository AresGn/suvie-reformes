<?php

namespace App\Listeners;

use App\Models\Session;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CreateSessionOnLogin
{
    protected $request;

    /**
     * Create the event listener.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // Log de debug pour confirmer que l'event listener est appelé
        Log::info('Event Listener CreateSessionOnLogin déclenché', [
            'user_id' => $event->user->id,
            'timestamp' => now()->toDateTimeString()
        ]);

        try {
            // Marquer les anciennes sessions actives de cet utilisateur comme inactives
            $this->deactivateOldSessions($event->user);

            // Créer une nouvelle session
            $session = Session::createSession($event->user, $this->request);

            // Log de l'événement pour audit (optionnel)
            Log::info('Nouvelle session créée', [
                'user_id' => $event->user->id,
                'session_id' => $session->session_id,
                'ip_address' => $session->ip_address,
                'login_at' => $session->login_at->toDateTimeString()
            ]);

            // Optionnel : Nettoyer les anciennes sessions (avec probabilité faible pour éviter l'impact sur les performances)
            if (rand(1, 100) <= 2) { // 2% de chance de nettoyer
                $this->cleanupOldSessions();
            }

        } catch (\Exception $e) {
            // Log l'erreur mais ne pas interrompre le processus de connexion
            Log::error('Erreur lors de la création de la session', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Désactiver les anciennes sessions actives de l'utilisateur
     */
    protected function deactivateOldSessions($user): void
    {
        Session::where('user_id', $user->id)
            ->where('status', 'active')
            ->update([
                'status' => 'inactive',
                'logout_at' => Carbon::now()
            ]);
    }

    /**
     * Nettoyer les anciennes sessions (garder seulement les 90 derniers jours)
     */
    protected function cleanupOldSessions(): void
    {
        try {
            $deletedCount = Session::cleanupOldSessions(90);
            
            if ($deletedCount > 0) {
                Log::info("Nettoyage des sessions : {$deletedCount} anciennes sessions supprimées");
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du nettoyage des sessions', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
