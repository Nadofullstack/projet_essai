<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RendezVous extends Model
{
    protected $fillable = [
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'duration',
        'location',
        'type',
        'status',
        'user_id',
        'participant_name',
        'participant_email',
        'participant_phone',
        'reminder',
        'reminder_time'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'reminder' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtenir l'utilisateur propriétaire du rendez-vous
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir le participant associé au rendez-vous
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    /**
     * Formater l'heure de début
     */
    public function getStartTimeAttribute($value): string
    {
        return $value ? date('H:i', strtotime($value)) : '';
    }

    /**
     * Formater l'heure de fin
     */
    public function getEndTimeAttribute($value): string
    {
        return $value ? date('H:i', strtotime($value)) : '';
    }

    /**
     * Obtenir la date formatée
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->date ? $this->date->format('d/m/Y') : '';
    }

    /**
     * Calculer la durée en heures
     */
    public function getDurationInHoursAttribute(): float
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }
        
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);
        
        return round(($end - $start) / 3600, 2);
    }
}
