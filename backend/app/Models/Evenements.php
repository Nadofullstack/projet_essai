<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evenements extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'type',
        'status',
        'user_id',
        'color',
        'is_recurring',
        'recurring_type',
        'recurring_end_date',
        'reminder',
        'reminder_time',
        'attendees',
        'max_attendees',
        'is_public',
        'tags',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_recurring' => 'boolean',
        'reminder' => 'boolean',
        'attendees' => 'array',
        'tags' => 'array',
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtenir l'utilisateur propriétaire de l'événement
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir la date de début formatée
     */
    public function getStartDateFormattedAttribute(): string
    {
        return $this->start_date ? $this->start_date->format('d/m/Y H:i') : '';
    }

    /**
     * Obtenir la date de fin formatée
     */
    public function getEndDateFormattedAttribute(): string
    {
        return $this->end_date ? $this->end_date->format('d/m/Y H:i') : '';
    }

    /**
     * Obtenir la date seule formatée
     */
    public function getDateOnlyFormattedAttribute(): string
    {
        return $this->start_date ? $this->start_date->format('d/m/Y') : '';
    }

    /**
     * Obtenir l'heure de début formatée
     */
    public function getStartTimeAttribute(): string
    {
        return $this->start_date ? $this->start_date->format('H:i') : '';
    }

    /**
     * Obtenir l'heure de fin formatée
     */
    public function getEndTimeAttribute(): string
    {
        return $this->end_date ? $this->end_date->format('H:i') : '';
    }

    /**
     * Obtenir la durée en heures
     */
    public function getDurationAttribute(): float
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        
        return round($this->end_date->diffInHours($this->start_date), 2);
    }

    /**
     * Obtenir le type formaté
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'meeting' => 'Réunion',
            'conference' => 'Conférence',
            'workshop' => 'Atelier',
            'webinar' => 'Webinaire',
            'social' => 'Événement social',
            'other' => 'Autre'
        ];
        return $labels[$this->type] ?? 'Autre';
    }

    /**
     * Obtenir le statut formaté
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'upcoming' => 'À venir',
            'ongoing' => 'En cours',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé'
        ];
        return $labels[$this->status] ?? 'Inconnu';
    }

    /**
     * Obtenir le nombre de participants
     */
    public function getAttendeeCountAttribute(): int
    {
        return is_array($this->attendees) ? count($this->attendees) : 0;
    }

    /**
     * Vérifier si l'événement est aujourd'hui
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->start_date && $this->start_date->isToday();
    }

    /**
     * Vérifier si l'événement est en cours
     */
    public function getIsOngoingAttribute(): bool
    {
        $now = now();
        return $this->start_date && $this->end_date && 
               $now->between($this->start_date, $this->end_date);
    }

    /**
     * Vérifier si l'événement est passé
     */
    public function getIsPastAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    /**
     * Vérifier si l'événement est à venir
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_date && $this->start_date->isFuture();
    }

    /**
     * Vérifier l'événement est complet
     */
    public function getIsFullAttribute(): bool
    {
        if (!$this->max_attendees) {
            return false;
        }
        
        return $this->attendee_count >= $this->max_attendees;
    }

    /**
     * Obtenir les tags formatés
     */
    public function getTagsFormattedAttribute(): string
    {
        if (!is_array($this->tags) || empty($this->tags)) {
            return '';
        }
        
        return implode(', ', $this->tags);
    }
}
