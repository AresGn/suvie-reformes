<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    /**
     * Afficher les sessions de l'utilisateur connecté
     */
    public function index()
    {
        $user = Auth::user();
        
        // Récupérer les sessions de l'utilisateur (30 derniers jours)
        $sessions = $user->recentSessions()->paginate(20);

        // Calculer les statistiques
        $stats = [
            'total' => $user->sessions()->count(),
            'active' => $user->activeSessions()->count(),
            'this_month' => $user->sessions()
                ->where('login_at', '>=', now()->startOfMonth())
                ->count(),
        ];

        return view('sessions.index', compact('sessions', 'stats'));
    }

    /**
     * API pour obtenir les statistiques de sessions
     */
    public function stats()
    {
        $user = Auth::user();
        
        $stats = [
            'total_sessions' => $user->sessions()->count(),
            'active_sessions' => $user->activeSessions()->count(),
            'sessions_today' => $user->sessions()
                ->whereDate('login_at', today())
                ->count(),
            'sessions_this_week' => $user->sessions()
                ->where('login_at', '>=', now()->startOfWeek())
                ->count(),
            'sessions_this_month' => $user->sessions()
                ->where('login_at', '>=', now()->startOfMonth())
                ->count(),
            'last_login' => $user->sessions()
                ->orderBy('login_at', 'desc')
                ->first()
                ?->login_at
                ?->format('d/m/Y à H:i'),
        ];

        return response()->json($stats);
    }

    /**
     * Fermer une session spécifique
     */
    public function terminate(Session $session)
    {
        // Vérifier que la session appartient à l'utilisateur connecté
        if ($session->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé');
        }

        // Empêcher la fermeture de la session actuelle
        if ($session->session_id === session()->getId()) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas fermer votre session actuelle.');
        }

        // Marquer la session comme inactive
        $session->markAsInactive();

        return redirect()->back()->with('success', 'Session fermée avec succès.');
    }
}
