<?php

// Test de debug pour vérifier la fonctionnalité des sessions
// À exécuter avec : php test_session_debug.php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Login;
use App\Models\User;
use App\Models\Session;

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Test de Debug - Gestion des Sessions\n";
echo "=====================================\n\n";

// Test 1: Vérifier que la table user_sessions existe
echo "Test 1: Vérification de la table user_sessions\n";
try {
    $tableExists = \Illuminate\Support\Facades\Schema::hasTable('user_sessions');
    if ($tableExists) {
        echo "✅ Table user_sessions existe\n";
        
        // Vérifier les colonnes
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('user_sessions');
        echo "📋 Colonnes: " . implode(', ', $columns) . "\n";
    } else {
        echo "❌ Table user_sessions n'existe pas - Exécutez: php artisan migrate\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification de la table: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Vérifier que l'EventServiceProvider est enregistré
echo "Test 2: Vérification de l'EventServiceProvider\n";
try {
    $providers = config('app.providers', []);
    $eventProviderExists = in_array('App\Providers\EventServiceProvider', $providers) || 
                          file_exists(base_path('bootstrap/providers.php'));
    
    if ($eventProviderExists) {
        echo "✅ EventServiceProvider configuré\n";
    } else {
        echo "❌ EventServiceProvider non configuré\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification du provider: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Vérifier que les listeners sont enregistrés
echo "Test 3: Vérification des Event Listeners\n";
try {
    $events = app('events');
    $listeners = $events->getListeners('Illuminate\Auth\Events\Login');
    
    if (!empty($listeners)) {
        echo "✅ Event Listeners pour Login trouvés: " . count($listeners) . "\n";
        foreach ($listeners as $listener) {
            if (is_string($listener)) {
                echo "   - " . $listener . "\n";
            } elseif (is_array($listener) && isset($listener[0])) {
                echo "   - " . get_class($listener[0]) . "\n";
            } else {
                echo "   - " . gettype($listener) . "\n";
            }
        }
    } else {
        echo "❌ Aucun Event Listener trouvé pour Login\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification des listeners: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Tester la création manuelle d'une session
echo "Test 4: Test de création manuelle d'une session\n";
try {
    // Créer un utilisateur de test ou utiliser le premier utilisateur
    $user = User::first();
    
    if ($user) {
        echo "👤 Utilisateur trouvé: ID " . $user->id . "\n";
        
        // Créer une session de test
        $sessionData = [
            'user_id' => $user->id,
            'session_id' => 'test_session_' . time(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
            'login_at' => now(),
            'last_activity' => now(),
            'status' => 'active'
        ];
        
        $session = Session::create($sessionData);
        echo "✅ Session créée manuellement: ID " . $session->id . "\n";
        
        // Nettoyer la session de test
        $session->delete();
        echo "🧹 Session de test supprimée\n";
        
    } else {
        echo "❌ Aucun utilisateur trouvé dans la base de données\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la création de session: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Simuler un événement Login
echo "Test 5: Simulation d'un événement Login\n";
try {
    $user = User::first();
    
    if ($user) {
        echo "🔄 Simulation de l'événement Login...\n";
        
        // Déclencher manuellement l'événement Login
        event(new Login('web', $user, false));
        
        echo "✅ Événement Login déclenché\n";
        
        // Vérifier si une session a été créée
        $latestSession = Session::where('user_id', $user->id)
                               ->orderBy('created_at', 'desc')
                               ->first();
        
        if ($latestSession && $latestSession->created_at->isAfter(now()->subMinute())) {
            echo "✅ Session créée automatiquement: ID " . $latestSession->id . "\n";
            echo "📊 Données de la session:\n";
            echo "   - User ID: " . $latestSession->user_id . "\n";
            echo "   - Session ID: " . $latestSession->session_id . "\n";
            echo "   - IP: " . $latestSession->ip_address . "\n";
            echo "   - Status: " . $latestSession->status . "\n";
            echo "   - Login At: " . $latestSession->login_at . "\n";
        } else {
            echo "❌ Aucune session créée automatiquement\n";
        }
        
    } else {
        echo "❌ Aucun utilisateur disponible pour le test\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la simulation: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 6: Vérifier les logs
echo "Test 6: Vérification des logs récents\n";
try {
    $logFile = storage_path('logs/laravel.log');
    
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $recentLogs = substr($logContent, -2000); // Derniers 2000 caractères
        
        if (strpos($recentLogs, 'CreateSessionOnLogin') !== false) {
            echo "✅ Logs de CreateSessionOnLogin trouvés\n";
        } else {
            echo "⚠️  Aucun log de CreateSessionOnLogin trouvé récemment\n";
        }
        
        if (strpos($recentLogs, 'Nouvelle session créée') !== false) {
            echo "✅ Logs de création de session trouvés\n";
        } else {
            echo "⚠️  Aucun log de création de session trouvé\n";
        }
    } else {
        echo "⚠️  Fichier de log non trouvé\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification des logs: " . $e->getMessage() . "\n";
}

echo "\n";
echo "🎯 Résumé du diagnostic:\n";
echo "========================\n";
echo "1. Exécutez 'php artisan migrate' si la table n'existe pas\n";
echo "2. Vérifiez les logs dans storage/logs/laravel.log\n";
echo "3. Testez une connexion réelle et vérifiez la table user_sessions\n";
echo "4. Si le problème persiste, vérifiez la configuration d'authentification\n";

echo "\n✅ Test de debug terminé\n";
