<?php

namespace App\Http\Controllers;

use App\Models\Evenements;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EvenementsController extends Controller
{
    /**
     * Afficher la liste des événements
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $events = Evenements::where('user_id', $user->id)
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Afficher un événement spécifique
     */
    public function show($id): JsonResponse
    {
        $event = Evenements::find($id);

        if (!$event || $event->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Événement non trouvé ou non autorisé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $event
        ]);
    }

    /**
     * Créer un nouvel événement
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'type' => 'required|in:meeting,conference,workshop,webinar,social,other',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
            'is_recurring' => 'boolean',
            'recurring_type' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurring_end_date' => 'nullable|date|after:start_date',
            'color' => 'nullable|string|max:7', // hex color
            'reminder' => 'boolean',
            'reminder_time' => 'nullable|integer|min:5|max:1440', // minutes before
            'attendees' => 'nullable|array',
            'attendees.*' => 'string|email|max:255'
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

        // Définir une couleur par défaut si non fournie
        if (!isset($data['color'])) {
            $colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];
            $data['color'] = $colors[array_rand($colors)];
        }

        $event = Evenements::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Événement créé avec succès',
            'data' => $event
        ], 201);
    }

    /**
     * Mettre à jour un événement
     */
    public function update(Request $request, $id): JsonResponse
    {
        $event = Evenements::find($id);

        if (!$event || $event->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Événement non trouvé ou non autorisé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'type' => 'sometimes|required|in:meeting,conference,workshop,webinar,social,other',
            'status' => 'sometimes|required|in:upcoming,ongoing,completed,cancelled',
            'is_recurring' => 'sometimes|boolean',
            'recurring_type' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurring_end_date' => 'nullable|date|after:start_date',
            'color' => 'nullable|string|max:7',
            'reminder' => 'sometimes|boolean',
            'reminder_time' => 'nullable|integer|min:5|max:1440',
            'attendees' => 'nullable|array',
            'attendees.*' => 'string|email|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $event->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Événement mis à jour avec succès',
            'data' => $event->fresh()
        ]);
    }

    /**
     * Supprimer un événement
     */
    public function destroy($id): JsonResponse
    {
        $event = Evenements::find($id);

        if (!$event || $event->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Événement non trouvé ou non autorisé'
            ], 404);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Événement supprimé avec succès'
        ]);
    }

    /**
     * Obtenir les événements d'une période
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

        $events = Evenements::where('user_id', $user->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Obtenir les événements à venir
     */
    public function upcoming(): JsonResponse
    {
        $user = Auth::user();
        $now = now();

        $events = Evenements::where('user_id', $user->id)
            ->where('start_date', '>=', $now)
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_date', 'asc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Obtenir les événements du jour
     */
    public function today(): JsonResponse
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $events = Evenements::where('user_id', $user->id)
            ->whereDate('start_date', $today)
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Obtenir les événements par type
     */
    public function byType($type): JsonResponse
    {
        $user = Auth::user();
        
        $events = Evenements::where('user_id', $user->id)
            ->where('type', $type)
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Obtenir les statistiques des événements
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        
        $totalEvents = Evenements::where('user_id', $user->id)->count();
        $upcomingEvents = Evenements::where('user_id', $user->id)
            ->where('status', 'upcoming')
            ->count();
        $completedEvents = Evenements::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();
        
        $eventsByType = Evenements::where('user_id', $user->id)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_events' => $totalEvents,
                'upcoming_events' => $upcomingEvents,
                'completed_events' => $completedEvents,
                'by_type' => $eventsByType
            ]
        ]);
    }
}
