<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Reforme;
use App\Models\Typereforme;
use App\Models\Activitesreformes;
use App\Models\Indicateur;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Middleware d'authentification
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Affiche le tableau de bord avec les statistiques
     */
    public function index()
    {
        // Statistiques de base
        $stats = [
            'totalUsers' => User::count(),
            'totalReformes' => Reforme::count(),
            'totalActivites' => Activitesreformes::count(),
            'totalIndicateurs' => Indicateur::count(),
        ];

        // Statistiques avancées (sans modifier la structure de la base de données)
        $stats['reformesParType'] = $this->getReformesParType();
        $stats['activitesRecentes'] = $this->getActivitesRecentes();
        
        // Pourcentages pour les barres de progression
        $stats['userPercent'] = 100;
        $stats['reformePercent'] = min(100, ($stats['totalReformes'] > 0) ? 100 : 0);
        $stats['activitePercent'] = min(100, ($stats['totalActivites'] > 0) ? 100 : 0);
        $stats['indicateurPercent'] = min(100, ($stats['totalIndicateurs'] > 0) ? 100 : 0);

        return view('dashboard', $stats);
    }

    /**
     * Récupère le nombre de réformes par type
     */
    private function getReformesParType()
    {
        return DB::table('reformes')
            ->join('type_reforme', 'reformes.type_reforme', '=', 'type_reforme.id')
            ->select('type_reforme.lib', DB::raw('count(*) as total'))
            ->groupBy('type_reforme.lib')
            ->get();
    }

    /**
     * Récupère les activités récentes
     */
    private function getActivitesRecentes()
    {
        return Activitesreformes::with(['reforme'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
}