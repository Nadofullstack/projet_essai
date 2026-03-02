<?php

namespace App\Http\Controllers;

use App\Models\Messages;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessagesController extends Controller
{
    /**
     * Afficher la liste des messages de l'utilisateur
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $messages = Messages::with(['sender', 'receiver'])
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Afficher un message spécifique
     */
    public function show($id): JsonResponse
    {
        $message = Messages::with(['sender', 'receiver', 'replies'])
            ->find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message non trouvé'
            ], 404);
        }

        // Vérifier si l'utilisateur a le droit de voir ce message
        $user = Auth::user();
        if ($message->sender_id !== $user->id && $message->receiver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé à voir ce message'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $message
        ]);
    }

    /**
     * Envoyer un nouveau message
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'type' => 'required|in:text,image,file,audio',
            'receiver_id' => 'required|exists:users,id',
            'attachment' => 'nullable|file|max:10240', // 10MB max
            'parent_id' => 'nullable|exists:messages,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->messages()
            ], 422);
        }

        $data = $validator->validated();
        $data['sender_id'] = Auth::id();
        $data['is_new'] = true; // Marquer comme nouveau message

        // Gérer les pièces jointes
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments', 'public');
            $data['attachment_path'] = $path;
            $data['attachment_type'] = $file->getClientOriginalExtension();
            $data['has_attachment'] = true;
        }

        $message = Messages::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Message envoyé avec succès',
            'data' => $message->load(['sender', 'receiver'])
        ], 201);
    }

    /**
     * Marquer un message comme lu
     */
    public function markAsRead($id): JsonResponse
    {
        $message = Messages::find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message non trouvé'
            ], 404);
        }

        $user = Auth::user();
        if ($message->receiver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé à modifier ce message'
            ], 403);
        }

        $message->update(['is_read' => true, 'is_new' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Message marqué comme lu'
        ]);
    }

    /**
     * Obtenir les messages non lus
     */
    public function unread(): JsonResponse
    {
        $user = Auth::user();
        $unreadCount = Messages::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        $unreadMessages = Messages::with(['sender'])
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $unreadCount,
                'messages' => $unreadMessages
            ]
        ]);
    }

    /**
     * Obtenir les conversations récentes (OPTIMISÉE)
     */
    public function conversations(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // ✅ OPTIMISÉ: Récupérer uniquement les colonnes nécessaires
            // ✅ Limiter à 500 messages pour éviter timeout
            $messages = Messages::select('id', 'sender_id', 'receiver_id', 'content', 'created_at', 'is_read')
                ->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)
                          ->orWhere('receiver_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->limit(500)
                ->get();

            // Grouper par conversation
            $conversations = [];
            $processedIds = [];

            foreach ($messages as $message) {
                $otherUserId = $message->sender_id === $user->id ? $message->receiver_id : $message->sender_id;
                
                if (!in_array($otherUserId, $processedIds)) {
                    // ✅ Charger l'utilisateur seulement si nécessaire
                    $otherUser = User::select('id', 'name', 'profile_picture')
                        ->find($otherUserId);
                    
                    if ($otherUser) {
                        // ✅ Compter les messages non lus de façon efficace
                        $unreadCount = Messages::where('receiver_id', $user->id)
                            ->where('sender_id', $otherUserId)
                            ->where('is_read', false)
                            ->count();

                        $conversations[] = [
                            'id' => $otherUserId,
                            'name' => $otherUser->name ?? 'Unknown',
                            'avatar' => $otherUser->profile_picture ?? null,
                            'lastMessage' => $message->content,
                            'last_message_time' => $message->created_at,
                            'unread_count' => $unreadCount
                        ];
                        $processedIds[] = $otherUserId;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => array_slice($conversations, 0, 20)  // ✅ Retourner max 20 au lieu de 10
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur conversations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des conversations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un message
     */
    public function destroy($id): JsonResponse
    {
        $message = Messages::find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message non trouvé'
            ], 404);
        }

        $user = Auth::user();
        if ($message->sender_id !== $user->id && $message->receiver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé à supprimer ce message'
            ], 403);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message supprimé avec succès'
        ]);
    }

    /**
     * Obtenir l'historique d'une conversation avec un utilisateur spécifique (AVEC PAGINATION)
     */
    public function getConversation($userId): JsonResponse
    {
        try {
            $currentUser = Auth::user();

            // Vérifier que l'utilisateur existe
            $otherUser = User::find($userId);
            if (!$otherUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // ✅ OPTIMISÉ: Récupérer UNE PAGINATION de messages (50 derniers)
            // ✅ Utiliser select() pour charger uniquement les colonnes nécessaires
            $messages = Messages::select('id', 'content', 'type', 'created_at', 'sender_id', 'receiver_id', 'is_read', 'attachment_path', 'attachment_type')
                ->where(function ($query) use ($currentUser, $userId) {
                    $query->where(function ($q) use ($currentUser, $userId) {
                        $q->where('sender_id', $currentUser->id)
                          ->where('receiver_id', $userId);
                    })
                    ->orWhere(function ($q) use ($currentUser, $userId) {
                        $q->where('sender_id', $userId)
                          ->where('receiver_id', $currentUser->id);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->limit(100)  // ✅ Limiter à 100 messages max
                ->get()
                ->reverse()  // Réordonner pour affichage chronologique
                ->map(function ($message) use ($currentUser) {
                    return [
                        'id' => $message->id,
                        'content' => $message->content,
                        'type' => $message->type ?? 'text',
                        'created_at' => $message->created_at,
                        'sender_id' => $message->sender_id,
                        'receiver_id' => $message->receiver_id,
                        'is_read' => (bool) $message->is_read,
                        'isSender' => $message->sender_id === $currentUser->id,
                        'status' => $message->is_read ? 'read' : 'sent',
                        'attachment_path' => $message->attachment_path ?? null,
                        'attachment_type' => $message->attachment_type ?? null
                    ];
                })
                ->values();

            // ✅ Marquer les messages reçus comme lus (async, ne pas attendre)
            Messages::where('receiver_id', $currentUser->id)
                ->where('sender_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'data' => $messages,
                'total_count' => Messages::where(function ($query) use ($currentUser, $userId) {
                    $query->where(function ($q) use ($currentUser, $userId) {
                        $q->where('sender_id', $currentUser->id)
                          ->where('receiver_id', $userId);
                    })
                    ->orWhere(function ($q) use ($currentUser, $userId) {
                        $q->where('sender_id', $userId)
                          ->where('receiver_id', $currentUser->id);
                    });
                })->count()  // Retourner le nombre total pour la pagination frontend
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur getConversation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la conversation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir la liste de tous les utilisateurs (sauf l'utilisateur courant)
     */
    public function listUsers(Request $request): JsonResponse
    {
        $user = Auth::user();
        $search = $request->query('search', '');

        $query = User::where('id', '!=', $user->id)
            ->select('id', 'name', 'email', 'profile_picture', 'online_status', 'last_seen_at', 'created_at');

        // Recherche par nom ou email si fourni
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $users = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Supprimer tous les messages "durs" de l'utilisateur
     * Effectue une suppression permanente (pas soft delete)
     */
    public function deleteAllMessages(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Compter les messages avant suppression
            $totalMessages = Messages::where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })->count();

            // Supprimer définitivement les messages
            Messages::where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })->forceDelete();

            return response()->json([
                'success' => true,
                'message' => "Tous vos {$totalMessages} messages ont été supprimés définitivement",
                'deleted_count' => $totalMessages
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur deleteAllMessages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression des messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer les messages "durs" avant une date spécifique
     * Utile pour nettoyer les anciens messages
     */
    public function deleteOldMessages(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'days_old' => 'required|integer|min:1|max:3650',
                'confirm' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->messages()
                ], 422);
            }

            // Vérification de sécurité - l'utilisateur doit confirmer
            if (!$request->get('confirm')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Suppression non confirmée'
                ], 400);
            }

            $user = Auth::user();
            $daysOld = $request->get('days_old');
            $cutoffDate = now()->subDays($daysOld);

            // Compter les messages avant suppression
            $totalMessages = Messages::where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->where('created_at', '<', $cutoffDate)
            ->count();

            // Supprimer définitivement les anciens messages
            Messages::where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->where('created_at', '<', $cutoffDate)
            ->forceDelete();

            return response()->json([
                'success' => true,
                'message' => "{$totalMessages} anciens messages (de plus de {$daysOld} jours) ont été supprimés définitivement",
                'deleted_count' => $totalMessages
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur deleteOldMessages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression des messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le statut en ligne de tous les utilisateurs
     */
    public function getUsersStatus(): JsonResponse
    {
        try {
            $users = User::select('id', 'name', 'online_status', 'last_seen_at')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur getUsersStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statuts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le statut en ligne de l'utilisateur courant
     */
    public function updateUserStatus(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'online_status' => 'required|in:online,offline'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->messages()
                ], 422);
            }

            $user = Auth::user();
            $status = $request->get('online_status');

            if ($status === 'online') {
                $user->markAsOnline();
            } else {
                $user->markAsOffline();
            }

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès',
                'data' => [
                    'online_status' => $user->online_status,
                    'last_seen_at' => $user->last_seen_at
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur updateUserStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}