<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\RendezVous;
use App\Models\Evenements;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    /**
     * Afficher le calendrier d'un mois
     */
    public function month(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2020|max:2050',
            'month' => 'required|integer|min:1|max:12'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $year = $request->year;
        $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
        
        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // Obtenir les rendez-vous du mois
        $rendezVous = RendezVous::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('start_time', 'asc')
            ->get();

        // Obtenir les événements du mois
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

        // Combiner et organiser par date
        $calendar = [];
        $daysInMonth = date('t', strtotime($startDate));

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = "{$year}-{$month}-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            $calendar[$date] = [
                'date' => $date,
                'day' => $day,
                'weekday' => date('N', strtotime($date)),
                'rendez_vous' => $rendezVous->where('date', $date)->values(),
                'events' => $events->filter(function ($event) use ($date) {
                    return $event->start_date->format('Y-m-d') === $date ||
                           ($event->start_date <= $date && $event->end_date >= $date);
                })->values(),
                'is_today' => $date === now()->format('Y-m-d'),
                'is_weekend' => in_array(date('N', strtotime($date)), [6, 7])
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'year' => (int)$year,
                'month' => (int)$request->month,
                'month_name' => date('F', strtotime($startDate)),
                'days' => array_values($calendar),
                'summary' => [
                    'total_rendez_vous' => $rendezVous->count(),
                    'total_events' => $events->count(),
                    'busy_days' => collect($calendar)->filter(function ($day) {
                        return $day['rendez_vous']->count() > 0 || $day['events']->count() > 0;
                    })->count()
                ]
            ]
        ]);
    }

    /**
     * Afficher le calendrier d'une semaine
     */
    public function week(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $date = $request->date;
        $startDate = date('Y-m-d', strtotime('monday this week', strtotime($date)));
        $endDate = date('Y-m-d', strtotime('sunday this week', strtotime($date)));

        // Obtenir les rendez-vous de la semaine
        $rendezVous = RendezVous::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Obtenir les événements de la semaine
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

        // Organiser par jour
        $week = [];
        for ($i = 0; $i < 7; $i++) {
            $currentDate = date('Y-m-d', strtotime($startDate . " +$i days"));
            $week[$currentDate] = [
                'date' => $currentDate,
                'day_name' => date('l', strtotime($currentDate)),
                'rendez_vous' => $rendezVous->where('date', $currentDate)->values(),
                'events' => $events->filter(function ($event) use ($currentDate) {
                    return $event->start_date->format('Y-m-d') === $currentDate ||
                           ($event->start_date <= $currentDate && $event->end_date >= $currentDate);
                })->values(),
                'is_today' => $currentDate === now()->format('Y-m-d')
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'week_number' => date('W', strtotime($startDate)),
                'days' => array_values($week)
            ]
        ]);
    }

    /**
     * Afficher le calendrier d'un jour
     */
    public function day(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $date = $request->date;

        // Obtenir les rendez-vous du jour
        $rendezVous = RendezVous::where('user_id', $user->id)
            ->where('date', $date)
            ->orderBy('start_time', 'asc')
            ->get();

        // Obtenir les événements du jour
        $events = Evenements::where('user_id', $user->id)
            ->where(function ($query) use ($date) {
                $query->whereDate('start_date', $date)
                      ->orWhereDate('end_date', $date)
                      ->orWhere(function ($q) use ($date) {
                          $q->where('start_date', '<=', $date)
                            ->where('end_date', '>=', $date);
                      });
            })
            ->orderBy('start_date', 'asc')
            ->get();

        // Créer un timeline horaire
        $timeline = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $time = sprintf('%02d:00', $hour);
            $timeline[$time] = [
                'time' => $time,
                'hour' => $hour,
                'rendez_vous' => $rendezVous->filter(function ($rdv) use ($hour) {
                    return (int)substr($rdv->start_time, 0, 2) === $hour;
                })->values(),
                'events' => $events->filter(function ($event) use ($hour) {
                    $eventHour = (int)$event->start_date->format('H');
                    return $eventHour === $hour;
                })->values()
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'day_name' => date('l', strtotime($date)),
                'is_today' => $date === now()->format('Y-m-d'),
                'rendez_vous' => $rendezVous,
                'events' => $events,
                'timeline' => array_values($timeline),
                'summary' => [
                    'total_rendez_vous' => $rendezVous->count(),
                    'total_events' => $events->count(),
                    'busy_hours' => collect($timeline)->filter(function ($slot) {
                        return $slot['rendez_vous']->count() > 0 || $slot['events']->count() > 0;
                    })->count()
                ]
            ]
        ]);
    }

    /**
     * Obtenir un aperçu du calendrier (prochains 7 jours)
     */
    public function overview(): JsonResponse
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');
        $nextWeek = date('Y-m-d', strtotime('+7 days'));

        // Obtenir les rendez-vous des 7 prochains jours
        $rendezVous = RendezVous::where('user_id', $user->id)
            ->whereBetween('date', [$today, $nextWeek])
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Obtenir les événements des 7 prochains jours
        $events = Evenements::where('user_id', $user->id)
            ->where('start_date', '>=', $today)
            ->where('start_date', '<=', $nextWeek)
            ->orderBy('start_date', 'asc')
            ->get();

        // Organiser par jour
        $overview = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime($today . " +$i days"));
            $overview[$date] = [
                'date' => $date,
                'day_name' => date('l', strtotime($date)),
                'rendez_vous' => $rendezVous->where('date', $date)->values(),
                'events' => $events->filter(function ($event) use ($date) {
                    return $event->start_date->format('Y-m-d') === $date;
                })->values(),
                'is_today' => $date === $today
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'period' => '7_days',
                'start_date' => $today,
                'end_date' => $nextWeek,
                'days' => array_values($overview),
                'summary' => [
                    'total_rendez_vous' => $rendezVous->count(),
                    'total_events' => $events->count(),
                    'busy_days' => collect($overview)->filter(function ($day) {
                        return $day['rendez_vous']->count() > 0 || $day['events']->count() > 0;
                    })->count()
                ]
            ]
        ]);
    }

    /**
     * Obtenir les statistiques du calendrier
     */
    public function stats(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period' => 'required|in:week,month,quarter,year',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $period = $request->period;

        // Définir les dates selon la période
        switch ($period) {
            case 'week':
                $startDate = $request->start_date ?? now()->startOfWeek()->format('Y-m-d');
                $endDate = $request->end_date ?? now()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
                $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
                break;
            case 'quarter':
                $startDate = $request->start_date ?? now()->startOfQuarter()->format('Y-m-d');
                $endDate = $request->end_date ?? now()->endOfQuarter()->format('Y-m-d');
                break;
            case 'year':
                $startDate = $request->start_date ?? now()->startOfYear()->format('Y-m-d');
                $endDate = $request->end_date ?? now()->endOfYear()->format('Y-m-d');
                break;
        }

        // Statistiques des rendez-vous
        $rendezVousStats = [
            'total' => RendezVous::where('user_id', $user->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->count(),
            'confirmed' => RendezVous::where('user_id', $user->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'confirmed')
                ->count(),
            'pending' => RendezVous::where('user_id', $user->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'pending')
                ->count(),
            'cancelled' => RendezVous::where('user_id', $user->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'cancelled')
                ->count()
        ];

        // Statistiques des événements
        $eventsStats = [
            'total' => Evenements::where('user_id', $user->id)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->count(),
            'completed' => Evenements::where('user_id', $user->id)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->count(),
            'upcoming' => Evenements::where('user_id', $user->id)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->where('status', 'upcoming')
                ->count(),
            'cancelled' => Evenements::where('user_id', $user->id)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->where('status', 'cancelled')
                ->count()
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'rendez_vous' => $rendezVousStats,
                'events' => $eventsStats,
                'total_activities' => $rendezVousStats['total'] + $eventsStats['total']
            ]
        ]);
    }
}
