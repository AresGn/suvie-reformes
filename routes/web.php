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

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('typereforme', TypereformeController::class)->except(['create', 'edit']);
Route::resource('reforme', ReformeController::class) ;
Route::resource('role', RoleController::class)->name('index', 'role');
Route::resource('indicateurs', IndicateurController::class);
Route::resource('utilisateurs', UserController::class);
// Routes pour les activités de réforme
Route::resource('activites', ActivitesreformesController::class);

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
