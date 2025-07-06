<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReformeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TypereformeController;
use App\Http\Controllers\IndicateurController;
use App\Http\Controllers\ActivitesreformesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuiviActivitesController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SuiviIndicateurController;


use App\Livewire\MenuComponent;
use App\Livewire\ReformeComponent;
use App\Livewire\TypereformeComponent;
use App\Models\SuiviActivites;

Route::get('/', function () {
    return view('Auth.login');
});

Route::get('/test', function () {
    return view('test');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*Route::get('/dashboard', function () {
    return view('dashboard'); // ou remplace par ta vue réelle
})->name('dashboard');*/

// Routes protégées par authentification
Route::middleware('auth')->group(function () {
    // Tableau de bord - accessible à tous les utilisateurs connectés
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Routes pour les réformes - nécessite permission de lecture
    Route::middleware('role.permission:permission:read_reformes')->group(function () {
        Route::get('reforme', [ReformeController::class, 'index'])->name('reformes.index');
        Route::get('reforme/{reforme}', [ReformeController::class, 'show'])->name('reformes.show');
    });

    // Routes pour créer/modifier les réformes - nécessite permission de création/modification
    Route::middleware('role.permission:permission:create_reformes')->group(function () {
        Route::get('reforme/create', [ReformeController::class, 'create'])->name('reformes.create');
        Route::post('reforme', [ReformeController::class, 'store'])->name('reformes.store');
    });

    Route::middleware('role.permission:permission:update_reformes')->group(function () {
        Route::get('reforme/{reforme}/edit', [ReformeController::class, 'edit'])->name('reformes.edit');
        Route::put('reforme/{reforme}', [ReformeController::class, 'update'])->name('reformes.update');
    });

    Route::middleware('role.permission:permission:delete_reformes')->group(function () {
        Route::delete('reforme/{reforme}', [ReformeController::class, 'destroy'])->name('reformes.destroy');
    });

    // Routes pour les activités - nécessite permission de lecture
    Route::middleware('role.permission:permission:read_activites')->group(function () {
        Route::get('activites', [ActivitesreformesController::class, 'index'])->name('activites.index');
        Route::get('activites/{activite}', [ActivitesreformesController::class, 'show'])->name('activites.show');
    });

    Route::middleware('role.permission:permission:create_activites')->group(function () {
        Route::get('activites/create', [ActivitesreformesController::class, 'create'])->name('activites.create');
        Route::post('activites', [ActivitesreformesController::class, 'store'])->name('activites.store');
    });

    Route::middleware('role.permission:permission:update_activites')->group(function () {
        Route::get('activites/{activite}/edit', [ActivitesreformesController::class, 'edit'])->name('activites.edit');
        Route::put('activites/{activite}', [ActivitesreformesController::class, 'update'])->name('activites.update');
    });

    Route::middleware('role.permission:permission:delete_activites')->group(function () {
        Route::delete('activites/{activite}', [ActivitesreformesController::class, 'destroy'])->name('activites.destroy');
    });

    // Routes pour les indicateurs - nécessite permission de lecture
    Route::middleware('role.permission:permission:read_indicateurs')->group(function () {
        Route::resource('indicateurs', IndicateurController::class)->only(['index', 'show']);
    });

    Route::middleware('role.permission:permission:manage_indicateurs')->group(function () {
        Route::resource('indicateurs', IndicateurController::class)->except(['index', 'show']);
    });

    // Routes pour les types de réforme - nécessite permission de gestion
    Route::middleware('role.permission:permission:manage_types_reforme')->group(function () {
        Route::resource('typereforme', TypereformeController::class)->except(['create', 'edit']);
    });

    // Routes d'administration - réservées aux administrateurs
    Route::middleware('role:admin,administrateur')->group(function () {
        Route::resource('utilisateurs', UserController::class);
        Route::resource('role', RoleController::class)->name('index', 'role');
    });
});

// Routes pour les sessions (optionnel - pour visualisation)
Route::middleware('auth')->group(function () {
    Route::get('sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::get('sessions/stats', [SessionController::class, 'stats'])->name('sessions.stats');
    Route::delete('sessions/{session}', [SessionController::class, 'terminate'])->name('sessions.terminate');
});



// Route de test pour diagnostiquer les problèmes CSRF
Route::get('/test-csrf', function() {
    return response()->json([
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId(),
        'session_lifetime' => config('session.lifetime'),
        'session_driver' => config('session.driver'),
        'app_key_set' => !empty(config('app.key')),
        'current_time' => now(),
        'session_data' => session()->all(),
        'message' => 'Test CSRF et session'
    ]);
});

// Route de test pour diagnostiquer le problème de statut 'P'
Route::get('/test-statut-creation', function() {
    try {
        // Test de création d'activité via le contrôleur
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'reforme_id' => 1,
            'libelle' => 'Test Création Statut - ' . now(),
            'date_debut' => now()->format('Y-m-d'),
            'date_fin_prevue' => now()->addMonths(6)->format('Y-m-d'),
            'poids' => 25,
            'structure_responsable' => 1,
        ]);

        // Simuler l'authentification
        \Auth::loginUsingId(1);

        // Test 1: Créer directement avec le modèle
        $activiteDirecte = \App\Models\Activitesreformes::create([
            'reforme_id' => 1,
            'libelle' => 'Test Direct - ' . now(),
            'date_debut' => now(),
            'date_fin_prevue' => now()->addMonths(6),
            'poids' => 30,
            'structure_responsable' => 1,
            'created_by' => 1,
        ]);

        // Test 2: Vérifier le statut immédiatement après création
        $statutImmediatApresCreation = $activiteDirecte->fresh()->statut;

        // Test 3: Attendre un moment et vérifier à nouveau
        sleep(1);
        $statutApresDelai = $activiteDirecte->fresh()->statut;

        // Test 4: Vérifier s'il y a des suivis automatiquement créés
        $suivisAutomatiques = \App\Models\SuiviActivites::where('activite_reforme_id', $activiteDirecte->id)->count();

        return response()->json([
            'success' => true,
            'test_creation_directe' => [
                'id' => $activiteDirecte->id,
                'statut_immediat' => $statutImmediatApresCreation,
                'statut_apres_delai' => $statutApresDelai,
                'suivis_automatiques' => $suivisAutomatiques
            ],
            'model_defaults' => [
                'attributes' => (new \App\Models\Activitesreformes())->getAttributes(),
                'fillable' => (new \App\Models\Activitesreformes())->getFillable()
            ],
            'message' => 'Test de création de statut terminé'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'message' => 'Erreur lors du test de statut par défaut'
        ]);
    }
});



// Routes pour les sous-activités
Route::prefix('activites/{activite}/sous-activites')->name('activites.sous-activites.')->group(function () {
    Route::get('/', [ActivitesreformesController::class, 'indexSousActivites'])->name('index');
    Route::get('/create', [ActivitesreformesController::class, 'createSousActivite'])->name('create');
    Route::post('/', [ActivitesreformesController::class, 'storeSousActivite'])->name('store');
    Route::get('/{sousActivite}/edit', [ActivitesreformesController::class, 'editSousActivite'])->name('edit');
    Route::put('/{sousActivite}', [ActivitesreformesController::class, 'updateSousActivite'])->name('update');
    Route::delete('/{sousActivite}', [ActivitesreformesController::class, 'destroySousActivite'])->name('destroy');

});

// Routes pour le suivi d'activités
Route::prefix('suivi-activites')->name('suivi-activites.')->group(function () {
    Route::get('/', [SuiviActivitesController::class, 'index'])->name('index');
    Route::post('/', [SuiviActivitesController::class, 'store'])->name('store');
    Route::post('/valider/{id}', [SuiviActivitesController::class, 'validerActivite'])->name('valider');
    Route::get('/historique/{id}', [SuiviActivitesController::class, 'historique'])->name('historique');
    Route::delete('/{id}', [SuiviActivitesController::class, 'destroy'])->name('destroy');
});

// Route API pour récupérer le poids restant d'une activité
Route::get('api/activites/{activite}/poids-restant', [ActivitesreformesController::class, 'getPoidsRestant'])->name('api.activites.poids-restant');

    // Routes pour les notifications - accessibles à tous les utilisateurs connectés
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // Page principale des notifications
        Route::get('/', [NotificationController::class, 'index'])->name('index');

        // API pour récupérer les notifications (AJAX)
        Route::get('/api', [NotificationController::class, 'getNotifications'])->name('api');

        // Obtenir le nombre de notifications non lues
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');

        // Marquer une notification comme lue
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');

        // Marquer toutes les notifications comme lues
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');

        // Afficher une notification et la marquer comme lue
        Route::get('/{id}', [NotificationController::class, 'show'])->name('show');

        // Supprimer une notification
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');

        // Supprimer toutes les notifications lues
        Route::delete('/delete-read', [NotificationController::class, 'deleteRead'])->name('delete-read');

        // Obtenir les statistiques des notifications
        Route::get('/stats', [NotificationController::class, 'getStats'])->name('stats');
    });

    // Routes pour les notifications (admin seulement)
    Route::middleware('role:admin')->prefix('notifications')->name('notifications.')->group(function () {
        // Créer une notification de test
        Route::post('/test', [NotificationController::class, 'createTest'])->name('test');

        // Envoyer une notification à tous les utilisateurs d'un rôle
        Route::post('/send-to-role', [NotificationController::class, 'sendToRole'])->name('send-to-role');
    });

    // Routes pour le suivi des indicateurs - accessibles aux gestionnaires et admins
    Route::middleware('role:gestionnaire,admin')->prefix('suivi-indicateurs')->name('suivi-indicateurs.')->group(function () {
        // Pages principales
        Route::get('/', [SuiviIndicateurController::class, 'index'])->name('index');
        Route::get('/reforme/{reforme}', [SuiviIndicateurController::class, 'tableauBordReforme'])->name('tableau-bord');
        Route::get('/alertes', [SuiviIndicateurController::class, 'alertes'])->name('alertes');

        // Gestion des associations indicateur-réforme
        Route::post('/reforme/{reforme}/associer', [SuiviIndicateurController::class, 'associerIndicateur'])->name('associer');
        Route::get('/reforme/{reforme}/indicateur/{indicateur}/dissocier', [SuiviIndicateurController::class, 'dissocierIndicateur'])->name('dissocier');

        // Gestion des évolutions
        Route::get('/{reformeIndicateur}/evolution/creer', [SuiviIndicateurController::class, 'creerEvolution'])->name('creer-evolution');
        Route::post('/{reformeIndicateur}/evolution', [SuiviIndicateurController::class, 'stockerEvolution'])->name('stocker-evolution');
        Route::get('/{reformeIndicateur}/evolution/{date}/modifier', [SuiviIndicateurController::class, 'modifierEvolution'])->name('modifier-evolution');
        Route::put('/{reformeIndicateur}/evolution/{date}', [SuiviIndicateurController::class, 'mettreAJourEvolution'])->name('mettre-a-jour-evolution');
        Route::get('/{reformeIndicateur}/evolution/{date}/supprimer', [SuiviIndicateurController::class, 'supprimerEvolution'])->name('supprimer-evolution');

        // Import/Export
        Route::get('/import-lot', [SuiviIndicateurController::class, 'importLot'])->name('import-lot');
        Route::post('/import-lot', [SuiviIndicateurController::class, 'traiterImportLot'])->name('traiter-import-lot');
        Route::get('/reforme/{reforme}/export-csv', [SuiviIndicateurController::class, 'exporterCSV'])->name('exporter-csv');

        // API pour les graphiques et données
        Route::get('/api/{reformeIndicateur}/graphique', [SuiviIndicateurController::class, 'apiDonneesGraphique'])->name('api.graphique');
        Route::get('/api/reforme/{reforme}/statistiques', [SuiviIndicateurController::class, 'apiStatistiquesReforme'])->name('api.statistiques');
    });

});
