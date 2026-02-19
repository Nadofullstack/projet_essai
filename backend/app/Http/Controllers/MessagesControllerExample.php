<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessagesController extends Controller
{
    /**
     * Récupérer la liste de tous les utilisateurs disponibles (connectés)
     * GET /api/messages/users/list
     */
    public function listUsers(Request $request)
    {
        try {
            $authUser = Auth::user();
            
            // Récupérer tous les utilisateurs sauf l'utilisateur connecté
            $users = User::where('id', '!=', $authUser->id)
                ->select('id', 'name', 'email', 'avatar', 'is_online')
                ->get()
                ->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar ?? "https://api.dicebear.com/7.x/avataaars/svg?seed={$user->email}",
                    'isOnline' => $user->is_online ?? false
                ]);

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des utilisateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les conversations de l'utilisateur connecté
     * GET /api/messages/conversations
     */
    public function conversations(Request $request)
    {
        try {
            $authUser = Auth::user();
            
            // Récupérer tous les utilisateurs avec lesquels il y a eu une conversation
            $conversations = Message::where('sender_id', $authUser->id)
                ->orWhere('receiver_id', $authUser->id)
                ->select('sender_id', 'receiver_id')
                ->distinct()
                ->get()
                ->map(fn($msg) => $msg->sender_id === $authUser->id ? $msg->receiver_id : $msg->sender_id)
                ->unique()
                ->values();

            $conversationData = [];
            foreach ($conversations as $userId) {
                $user = User::find($userId);
                
                // Récupérer le dernier message
                $lastMessage = Message::where(function($q) use ($authUser, $userId) {
                    $q->where(['sender_id' => $authUser->id, 'receiver_id' => $userId])
                      ->orWhere(['sender_id' => $userId, 'receiver_id' => $authUser->id]);
                })
                ->latest('created_at')
                ->first();

                $unreadCount = Message::where('sender_id', $userId)
                    ->where('receiver_id', $authUser->id)
                    ->where('is_read', false)
                    ->count();

                $conversationData[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar ?? "https://api.dicebear.com/7.x/avataaars/svg?seed={$user->email}",
                    'lastMessage' => $lastMessage?->content ?? '',
                    'lastMessageAt' => $lastMessage?->created_at->toIso8601String(),
                    'unreadCount' => $unreadCount,
                    'isOnline' => $user->is_online ?? false
                ];
            }

            return response()->json([
                'success' => true,
                'conversations' => $conversationData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des conversations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les messages avec un utilisateur spécifique
     * GET /api/messages/{userId}
     */
    public function show(Request $request, $userId)
    {
        try {
            $authUser = Auth::user();

            // Récupérer tous les messages entre les deux utilisateurs
            $messages = Message::where(function($q) use ($authUser, $userId) {
                $q->where(['sender_id' => $authUser->id, 'receiver_id' => $userId])
                  ->orWhere(['sender_id' => $userId, 'receiver_id' => $authUser->id]);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'senderName' => $msg->sender->name,
                'receiver_id' => $msg->receiver_id,
                'content' => $msg->content,
                'created_at' => $msg->created_at->toIso8601String(),
                'is_read' => $msg->is_read,
                'read_at' => $msg->read_at?->toIso8601String()
            ]);

            // Marquer les messages reçus comme lus
            Message::where('sender_id', $userId)
                ->where('receiver_id', $authUser->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            return response()->json([
                'success' => true,
                'messages' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer un message
     * POST /api/messages
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'content' => 'required|string'
            ]);

            $authUser = Auth::user();

            // Vérifier que l'utilisateur n'envoie pas à lui-même
            if ($validated['receiver_id'] == $authUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas vous envoyer un message'
                ], 422);
            }

            // Créer le message
            $message = Message::create([
                'sender_id' => $authUser->id,
                'receiver_id' => $validated['receiver_id'],
                'content' => $validated['content']
            ]);

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'senderName' => $message->sender->name,
                    'receiver_id' => $message->receiver_id,
                    'content' => $message->content,
                    'created_at' => $message->created_at->toIso8601String()
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer un message comme lu
     * PUT /api/messages/{messageId}/read
     */
    public function markAsRead(Request $request, $messageId)
    {
        try {
            $authUser = Auth::user();

            $message = Message::findOrFail($messageId);

            // Vérifier que l'utilisateur est le destinataire
            if ($message->receiver_id !== $authUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non autorisé'
                ], 403);
            }

            $message->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message marqué comme lu'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les messages non lus
     * GET /api/messages/unread
     */
    public function unread(Request $request)
    {
        try {
            $authUser = Auth::user();

            $unreadCount = Message::where('receiver_id', $authUser->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'unreadCount' => $unreadCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
