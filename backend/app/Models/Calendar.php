<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calendar extends Model
{
    protected $fillable = [
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'all_day',
        'type',
        'location',
        'user_id',
        'color',
        'reminder',
        'reminder_time',
        'is_recurring',
        'recurring_pattern',
        'recurring_end_date',
        'visibility', // public, private, shared
        'attendees'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'all_day' => 'boolean',
        'reminder' => 'boolean',
        'is_recurring' => 'boolean',
        'attendees' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtenir l'utilisateur propriétaire du calendrier
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir l'heure de début formatée
     */
    public function getStartTimeFormattedAttribute(): string
    {
        return $this->start_time ? $this->start_time->format('H:i') : '';
    }

    /**
     * Obtenir l'heure de fin formatée
     */
    public function getEndTimeFormattedAttribute(): string
    {
        return $this->end_time ? $this->end_time->format('H:i') : '';
    }

    /**
     * Obtenir la date formatée
     */
    public function getDateFormattedAttribute(): string
    {
        return $this->date ? $this->date->format('d/m/Y') : '';
    }

    /**
     * Obtenir la durée en minutes
     */
    public function getDurationAttribute(): int
    {
        if (!$this->start_time || !$this->end_time || $this->all_day) {
            return 0;
        }
        
        return $this->end_time->diffInMinutes($this->start_time);
    }

    /**
     * Obtenir le type formaté
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'meeting' => 'Réunion',
            'appointment' => 'Rendez-vous',
            'task' => 'Tâche',
            'reminder' => 'Rappel',
            'birthday' => 'Anniversaire',
            'holiday' => 'Vacances',
            'other' => 'Autre'
        ];
        return $labels[$this->type] ?? 'Autre';
    }

    /**
     * Vérifier si l'événement est aujourd'hui
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->date && $this->date->isToday();
    }

    /**
     * Vérifier si l'événement est passé
     */
    public function getIsPastAttribute(): bool
    {
        return $this->date && $this->date->isPast();
    }

    /**
     * Vérifier si l'événement est à venir
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->date && $this->date->isFuture();
    }
}
