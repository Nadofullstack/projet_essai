<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RendezVousController extends Controller
{
    /**
     * Afficher la liste des rendez-vous
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $rendezVous = RendezVous::where('user_id', $user->id)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rendezVous
        ]);
    }

    /**
     * Afficher un rendez-vous spécifique
     */
    public function show($id): JsonResponse
    {
        $rendezVous = RendezVous::with(['user', 'participant'])
            ->find($id);

        if (!$rendezVous) {
            return response()->json([
                'success' => false,
                'message' => 'Rendez-vous non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $rendezVous
        ]);
    }

    /**
     * Créer un nouveau rendez-vous
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|numeric|min:0.5|max:8',
            'location' => 'nullable|string|max:255',
            'type' => 'required|in:consultation,meeting,call,video,personal',
            'status' => 'required|in:pending,confirmed,cancelled',
            'participant_name' => 'required|string|max:255',
            'participant_email' => 'nullable|email|max:255',
            'participant_phone' => 'nullable|string|max:20',
            'reminder' => 'boolean'
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

        // Calculer l'heure de fin automatiquement
        $startTime = $data['date'] . ' ' . $data['start_time'];
        $duration = (float) $data['duration'];
        $endTime = date('Y-m-d H:i:s', strtotime($startTime) + ($duration * 3600));
        
        $data['end_time'] = $endTime;

        $rendezVous = RendezVous::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous créé avec succès',
            'data' => $rendezVous
        ], 201);
    }

    /**
     * Mettre à jour un rendez-vous
     */
    public function update(Request $request, $id): JsonResponse
    {
        $rendezVous = RendezVous::find($id);

        if (!$rendezVous || $rendezVous->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Rendez-vous non trouvé ou non autorisé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'sometimes|required|date|after_or_equal:today',
            'start_time' => 'sometimes|required|date_format:H:i',
            'duration' => 'sometimes|required|numeric|min:0.5|max:8',
            'location' => 'nullable|string|max:255',
            'type' => 'sometimes|required|in:consultation,meeting,call,video,personal',
            'status' => 'sometimes|required|in:pending,confirmed,cancelled',
            'participant_name' => 'sometimes|required|string|max:255',
            'participant_email' => 'nullable|email|max:255',
            'participant_phone' => 'nullable|string|max:20',
            'reminder' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Recalculer l'heure de fin si la durée change
        if (isset($data['start_time']) && isset($data['duration'])) {
            $startTime = $data['date'] . ' ' . $data['start_time'];
            $duration = (float) $data['duration'];
            $endTime = date('Y-m-d H:i:s', strtotime($startTime) + ($duration * 3600));
            $data['end_time'] = $endTime;
        }

        $rendezVous->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous mis à jour avec succès',
            'data' => $rendezVous->fresh()
        ]);
    }

    /**
     * Supprimer un rendez-vous
     */
    public function destroy($id): JsonResponse
    {
        $rendezVous = RendezVous::find($id);

        if (!$rendezVous || $rendezVous->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Rendez-vous non trouvé ou non autorisé'
            ], 404);
        }

        $rendezVous->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous supprimé avec succès'
        ]);
    }

    /**
     * Obtenir les rendez-vous du jour
     */
    public function today(): JsonResponse
    {
        $user = Auth::user();
        $today = date('Y-m-d');
        
        $rendezVous = RendezVous::where('user_id', $user->id)
            ->where('date', $today)
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rendezVous
        ]);
    }

    /**
     * Obtenir les rendez-vous d'une période
     */
    public function byPeriod(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $rendezVous = RendezVous::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rendezVous
        ]);
    }

    /**
     * Confirmer un rendez-vous
     */
    public function confirm($id): JsonResponse
    {
        $rendezVous = RendezVous::find($id);

        if (!$rendezVous || $rendezVous->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Rendez-vous non trouvé ou non autorisé'
            ], 404);
        }

        $rendezVous->update(['status' => 'confirmed']);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous confirmé avec succès',
            'data' => $rendezVous
        ]);
    }

    /**
     * Annuler un rendez-vous
     */
    public function cancel($id): JsonResponse
    {
        $rendezVous = RendezVous::find($id);

        if (!$rendezVous || $rendezVous->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Rendez-vous non trouvé ou non autorisé'
            ], 404);
        }

        $rendezVous->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous annulé avec succès',
            'data' => $rendezVous
        ]);
    }
}
