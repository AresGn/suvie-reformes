<?php

namespace App\Listeners;

use App\Models\Session;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateSessionOnLogout
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        try {
            // Récupérer l'ID de session Laravel actuel
            $sessionId = session()->getId();
            
            // Trouver la session correspondante dans notre table
            $session = Session::where('user_id', $event->user->id)
                ->where('session_id', $sessionId)
                ->where('status', 'active')
                ->first();
            
            if ($session) {
                // Marquer la session comme inactive
                $session->markAsInactive();
                
                Log::info('Session fermée', [
                    'user_id' => $event->user->id,
                    'session_id' => $session->session_id,
                    'login_at' => $session->login_at->toDateTimeString(),
                    'logout_at' => $session->logout_at->toDateTimeString(),
                    'duration_minutes' => $session->duration
                ]);
            } else {
                // Fallback : marquer toutes les sessions actives de l'utilisateur comme fermées
                $updatedSessions = Session::where('user_id', $event->user->id)
                    ->where('status', 'active')
                    ->update([
                        'status' => 'inactive',
                        'logout_at' => Carbon::now()
                    ]);

                if ($updatedSessions > 0) {
                    Log::info('Sessions fermées (fallback)', [
                        'user_id' => $event->user->id,
                        'sessions_closed' => $updatedSessions
                    ]);
                }
            }

        } catch (\Exception $e) {
            // Log l'erreur mais ne pas interrompre le processus de déconnexion
            Log::error('Erreur lors de la mise à jour de la session', [
                'user_id' => $event->user->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
