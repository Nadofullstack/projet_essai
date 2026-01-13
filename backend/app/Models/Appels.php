<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appels extends Model
{
    protected $fillable = [
        'contact_name',
        'contact_phone',
        'contact_email',
        'type',
        'status',
        'duration',
        'start_time',
        'end_time',
        'notes',
        'recording_path',
        'recording_size',
        'cost',
        'is_favorite',
        'user_id',
        'call_direction', // inbound, outbound
        'call_quality', // excellent, good, fair, poor
        'network_type', // wifi, mobile, landline
        'international',
        'country_code',
        'missed_reason' // busy, unavailable, rejected
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration' => 'integer',
        'recording_size' => 'integer',
        'cost' => 'decimal:2',
        'is_favorite' => 'boolean',
        'international' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtenir l'utilisateur propriétaire de l'appel
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
        return $this->start_time ? $this->start_time->format('d/m/Y H:i') : '';
    }

    /**
     * Obtenir la date seule formatée
     */
    public function getDateOnlyFormattedAttribute(): string
    {
        return $this->start_time ? $this->start_time->format('d/m/Y') : '';
    }

    /**
     * Obtenir l'heure seule formatée
     */
    public function getTimeOnlyFormattedAttribute(): string
    {
        return $this->start_time ? $this->start_time->format('H:i') : '';
    }

    /**
     * Obtenir la durée formatée
     */
    public function getDurationFormattedAttribute(): string
    {
        if (!$this->duration || $this->duration <= 0) {
            return '00:00';
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Obtenir la durée en minutes
     */
    public function getDurationMinutesAttribute(): int
    {
        if (!$this->duration) {
            return 0;
        }

        return round($this->duration / 60);
    }

    /**
     * Obtenir le type formaté
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'incoming' => 'Appel entrant',
            'outgoing' => 'Appel sortant',
            'missed' => 'Appel manqué'
        ];
        return $labels[$this->type] ?? 'Inconnu';
    }

    /**
     * Obtenir le statut formaté
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'ringing' => 'En sonnerie',
            'ongoing' => 'En cours',
            'completed' => 'Terminé',
            'missed' => 'Manqué',
            'failed' => 'Échoué'
        ];
        return $labels[$this->status] ?? 'Inconnu';
    }

    /**
     * Obtenir la qualité formatée
     */
    public function getQualityLabelAttribute(): string
    {
        $labels = [
            'excellent' => 'Excellente',
            'good' => 'Bonne',
            'fair' => 'Moyenne',
            'poor' => 'Mauvaise'
        ];
        return $labels[$this->call_quality] ?? 'Non évaluée';
    }

    /**
     * Obtenir le type de réseau formaté
     */
    public function getNetworkLabelAttribute(): string
    {
        $labels = [
            'wifi' => 'Wi-Fi',
            'mobile' => 'Mobile',
            'landline' => 'Fixe'
        ];
        return $labels[$this->network_type] ?? 'Inconnu';
    }

    /**
     * Obtenir le numéro formaté
     */
    public function getFormattedPhoneAttribute(): string
    {
        if (!$this->contact_phone) {
            return '';
        }

        $phone = $this->contact_phone;
        
        // Ajouter le code pays si international
        if ($this->international && $this->country_code) {
            $phone = '+' . $this->country_code . ' ' . $phone;
        }

        return $phone;
    }

    /**
     * Vérifier si l'appel est aujourd'hui
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->start_time && $this->start_time->isToday();
    }

    /**
     * Vérifier si l'appel est cette semaine
     */
    public function getIsThisWeekAttribute(): bool
    {
        return $this->start_time && $this->start_time->isCurrentWeek();
    }

    /**
     * Vérifier si l'appel est manqué
     */
    public function getIsMissedCallAttribute(): bool
    {
        return $this->status === 'missed' || $this->type === 'missed';
    }

    /**
     * Vérifier si l'appel a un enregistrement
     */
    public function getHasRecordingAttribute(): bool
    {
        return !empty($this->recording_path);
    }

    /**
     * Obtenir la taille du fichier formatée
     */
    public function getRecordingSizeFormattedAttribute(): string
    {
        if (!$this->recording_size) {
            return '0 B';
        }

        $bytes = $this->recording_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
