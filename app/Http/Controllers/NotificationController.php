<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->middleware('auth');
    }

    /**
     * Afficher la liste des notifications de l'utilisateur
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $notifications = $this->notificationService->getUserNotifications($user->id, 50);
        $stats = $this->notificationService->getNotificationStats($user->id);

        return view('notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Obtenir les notifications pour l'API (AJAX)
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = $request->get('limit', 10);
        $onlyUnread = $request->get('unread', false);

        if ($onlyUnread) {
            $notifications = $this->notificationService->getUserUnreadNotifications($user->id, $limit);
        } else {
            $notifications = $this->notificationService->getUserNotifications($user->id, $limit);
        }

        $unreadCount = $this->notificationService->getUnreadCount($user->id);

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'url' => $notification->url,
                    'date_notification' => $notification->date_notification->format('Y-m-d H:i:s'),
                    'time_ago' => $notification->time_ago,
                    'statut' => $notification->statut,
                    'is_read' => $notification->isRead(),
                    'icon' => $notification->icon,
                    'color' => $notification->color,
                ];
            }),
            'unread_count' => $unreadCount,
            'total' => $notifications->count()
        ]);
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function getUnreadCount(): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->getUnreadCount($user->id);

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $success = $this->notificationService->markAsRead($id, $user->id);

        if ($success) {
            $unreadCount = $this->notificationService->getUnreadCount($user->id);
            return response()->json([
                'success' => true,
                'message' => 'Notification marquée comme lue',
                'unread_count' => $unreadCount
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification non trouvée'
        ], 404);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $this->notificationService->markAllAsRead($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications ont été marquées comme lues',
            'unread_count' => 0
        ]);
    }

    /**
     * Supprimer une notification
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        $success = $this->notificationService->deleteNotification($id, $user->id);

        if ($success) {
            $unreadCount = $this->notificationService->getUnreadCount($user->id);
            return response()->json([
                'success' => true,
                'message' => 'Notification supprimée',
                'unread_count' => $unreadCount
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification non trouvée'
        ], 404);
    }

    /**
     * Supprimer toutes les notifications lues
     */
    public function deleteRead(): JsonResponse
    {
        $user = Auth::user();
        $deletedCount = $this->notificationService->deleteReadNotifications($user->id);

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} notification(s) supprimée(s)",
            'deleted_count' => $deletedCount
        ]);
    }

    /**
     * Afficher une notification spécifique et la marquer comme lue
     */
    public function show(int $id)
    {
        $user = Auth::user();
        
        // Marquer comme lue
        $this->notificationService->markAsRead($id, $user->id);
        
        // Récupérer la notification pour obtenir l'URL
        $notification = \App\Models\Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($notification && $notification->url) {
            return redirect($notification->url);
        }

        // Si pas d'URL, rediriger vers la liste des notifications
        return redirect()->route('notifications.index');
    }

    /**
     * Obtenir les statistiques des notifications
     */
    public function getStats(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->notificationService->getNotificationStats($user->id);

        return response()->json($stats);
    }

    /**
     * Créer une notification de test (pour les administrateurs)
     */
    public function createTest(Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur est admin
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $request->validate([
            'message' => 'required|string|max:255',
            'url' => 'nullable|url',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $userId = $request->user_id ?? Auth::id();
        $notification = $this->notificationService->createNotification(
            $userId,
            $request->message,
            $request->url
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification de test créée',
            'notification' => [
                'id' => $notification->id,
                'message' => $notification->message,
                'url' => $notification->url,
                'date_notification' => $notification->date_notification->format('Y-m-d H:i:s')
            ]
        ]);
    }

    /**
     * Envoyer une notification à tous les utilisateurs d'un rôle (pour les administrateurs)
     */
    public function sendToRole(Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur est admin
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $request->validate([
            'role' => 'required|string|exists:roles,role_name',
            'message' => 'required|string|max:255',
            'url' => 'nullable|url'
        ]);

        $success = $this->notificationService->createNotificationForRole(
            $request->role,
            $request->message,
            $request->url
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => "Notification envoyée à tous les utilisateurs du rôle '{$request->role}'"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'envoi de la notification'
        ], 500);
    }
}
