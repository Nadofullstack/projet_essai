<?php

namespace App\Http\Controllers;

use App\Models\Appels;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AppelsController extends Controller
{
    /**
     * Afficher la liste des appels
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $calls = Appels::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $calls
        ]);
    }

    /**
     * Afficher un appel spécifique
     */
    public function show($id): JsonResponse
    {
        $call = Appels::find($id);

        if (!$call || $call->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Appel non trouvé ou non autorisé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $call
        ]);
    }

    /**
     * Créer un nouvel appel
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'type' => 'required|in:incoming,outgoing,missed',
            'status' => 'required|in:ringing,ongoing,completed,missed,failed',
            'duration' => 'nullable|integer|min:0',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'notes' => 'nullable|string',
            'recording_file' => 'nullable|file|mimes:mp3,wav,m4a|max:10240', // 10MB max
            'cost' => 'nullable|numeric|min:0',
            'is_favorite' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = Auth::id();

        // Gérer le fichier d'enregistrement
        if ($request->hasFile('recording_file')) {
            $file = $request->file('recording_file');
            $path = $file->store('call_recordings', 'public');
            $data['recording_path'] = $path;
            $data['recording_size'] = $file->getSize();
        }

        // Calculer la durée automatiquement si non fournie
        if (!isset($data['duration']) && isset($data['end_time'])) {
            $startTime = strtotime($data['start_time']);
            $endTime = strtotime($data['end_time']);
            $data['duration'] = $endTime - $startTime;
        }

        $call = Appels::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Appel créé avec succès',
            'data' => $call
        ], 201);
    }

    /**
     * Mettre à jour un appel
     */
    public function update(Request $request, $id): JsonResponse
    {
        $call = Appels::find($id);

        if (!$call || $call->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Appel non trouvé ou non autorisé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'contact_name' => 'sometimes|required|string|max:255',
            'contact_phone' => 'sometimes|required|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'type' => 'sometimes|required|in:incoming,outgoing,missed',
            'status' => 'sometimes|required|in:ringing,ongoing,completed,missed,failed',
            'duration' => 'nullable|integer|min:0',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'notes' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'is_favorite' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $call->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Appel mis à jour avec succès',
            'data' => $call->fresh()
        ]);
    }

    /**
     * Supprimer un appel
     */
    public function destroy($id): JsonResponse
    {
        $call = Appels::find($id);

        if (!$call || $call->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Appel non trouvé ou non autorisé'
            ], 404);
        }

        $call->delete();

        return response()->json([
            'success' => true,
            'message' => 'Appel supprimé avec succès'
        ]);
    }

    /**
     * Obtenir les appels récents
     */
    public function recent(): JsonResponse
    {
        $user = Auth::user();
        $recentCalls = Appels::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recentCalls
        ]);
    }

    /**
     * Obtenir les appels du jour
     */
    public function today(): JsonResponse
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $calls = Appels::where('user_id', $user->id)
            ->whereDate('start_time', $today)
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $calls
        ]);
    }

    /**
     * Obtenir les appels par type
     */
    public function byType($type): JsonResponse
    {
        $user = Auth::user();
        
        $calls = Appels::where('user_id', $user->id)
            ->where('type', $type)
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $calls
        ]);
    }

    /**
     * Obtenir les appels par statut
     */
    public function byStatus($status): JsonResponse
    {
        $user = Auth::user();
        
        $calls = Appels::where('user_id', $user->id)
            ->where('status', $status)
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $calls
        ]);
    }

    /**
     * Obtenir les appels manqués
     */
    public function missed(): JsonResponse
    {
        $user = Auth::user();
        
        $missedCalls = Appels::where('user_id', $user->id)
            ->where('status', 'missed')
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $missedCalls
        ]);
    }

    /**
     * Obtenir les appels favoris
     */
    public function favorites(): JsonResponse
    {
        $user = Auth::user();
        
        $favoriteCalls = Appels::where('user_id', $user->id)
            ->where('is_favorite', true)
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $favoriteCalls
        ]);
    }

    /**
     * Marquer un appel comme favori
     */
    public function toggleFavorite($id): JsonResponse
    {
        $call = Appels::find($id);

        if (!$call || $call->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Appel non trouvé ou non autorisé'
            ], 404);
        }

        $call->update(['is_favorite' => !$call->is_favorite]);

        return response()->json([
            'success' => true,
            'message' => $call->is_favorite ? 'Appel ajouté aux favoris' : 'Appel retiré des favoris',
            'data' => $call
        ]);
    }

    /**
     * Obtenir les statistiques des appels
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        
        $totalCalls = Appels::where('user_id', $user->id)->count();
        $incomingCalls = Appels::where('user_id', $user->id)->where('type', 'incoming')->count();
        $outgoingCalls = Appels::where('user_id', $user->id)->where('type', 'outgoing')->count();
        $missedCalls = Appels::where('user_id', $user->id)->where('status', 'missed')->count();
        $totalDuration = Appels::where('user_id', $user->id)->sum('duration');
        $totalCost = Appels::where('user_id', $user->id)->sum('cost');
        
        $callsByType = Appels::where('user_id', $user->id)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_calls' => $totalCalls,
                'incoming_calls' => $incomingCalls,
                'outgoing_calls' => $outgoingCalls,
                'missed_calls' => $missedCalls,
                'total_duration' => $totalDuration,
                'total_cost' => $totalCost,
                'by_type' => $callsByType
            ]
        ]);
    }

    /**
     * Rechercher des appels
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'type' => 'nullable|in:incoming,outgoing,missed',
            'status' => 'nullable|in:ringing,ongoing,completed,missed,failed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $query = $request->query;
        $type = $request->type;
        $status = $request->status;

        $calls = Appels::where('user_id', $user->id)
            ->where(function ($q) use ($query) {
                $q->where('contact_name', 'LIKE', "%{$query}%")
                  ->orWhere('contact_phone', 'LIKE', "%{$query}%")
                  ->orWhere('contact_email', 'LIKE', "%{$query}%")
                  ->orWhere('notes', 'LIKE', "%{$query}%");
            })
            ->when($type, function ($q) use ($type) {
                return $q->where('type', $type);
            })
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $calls
        ]);
    }
}
