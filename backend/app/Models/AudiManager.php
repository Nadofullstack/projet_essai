<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiManager extends Model
{
    protected $fillable = [
        'title',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'duration',
        'quality',
        'format',
        'category',
        'tags',
        'user_id',
        'is_public',
        'is_favorite',
        'play_count',
        'download_count',
        'waveform_data',
        'transcription',
        'language',
        'sample_rate',
        'bitrate',
        'channels',
        'recorded_at'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'duration' => 'integer',
        'is_public' => 'boolean',
        'is_favorite' => 'boolean',
        'play_count' => 'integer',
        'download_count' => 'integer',
        'tags' => 'array',
        'waveform_data' => 'array',
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtenir l'utilisateur propriétaire de l'enregistrement
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir la date d'enregistrement formatée
     */
    public function getRecordedAtFormattedAttribute(): string
    {
        return $this->recorded_at ? $this->recorded_at->format('d/m/Y H:i') : '';
    }

    /**
     * Obtenir la date de création formatée
     */
    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('d/m/Y H:i') : '';
    }

    /**
     * Obtenir la durée formatée
     */
    public function getDurationFormattedAttribute(): string
    {
        if (!$this->duration || $this->duration <= 0) {
            return '00:00';
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Obtenir la durée en minutes
     */
    public function getDurationMinutesAttribute(): float
    {
        if (!$this->duration) {
            return 0;
        }

        return round($this->duration / 60, 2);
    }

    /**
     * Obtenir la qualité formatée
     */
    public function getQualityLabelAttribute(): string
    {
        $labels = [
            'low' => 'Basse',
            'medium' => 'Moyenne',
            'high' => 'Haute',
            'studio' => 'Studio'
        ];
        return $labels[$this->quality] ?? 'Inconnue';
    }

    /**
     * Obtenir le format formaté
     */
    public function getFormatLabelAttribute(): string
    {
        $labels = [
            'mp3' => 'MP3',
            'wav' => 'WAV',
            'm4a' => 'M4A',
            'ogg' => 'OGG'
        ];
        return $labels[$this->format] ?? strtoupper($this->format);
    }

    /**
     * Obtenir la catégorie formatée
     */
    public function getCategoryLabelAttribute(): string
    {
        $labels = [
            'voice_note' => 'Note vocale',
            'meeting' => 'Réunion',
            'lecture' => 'Cours',
            'podcast' => 'Podcast',
            'music' => 'Musique',
            'interview' => 'Interview',
            'other' => 'Autre'
        ];
        return $labels[$this->category] ?? 'Autre';
    }

    /**
     * Obtenir la taille du fichier formatée
     */
    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) {
            return '0 B';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Obtenir le bitrate formaté
     */
    public function getBitrateFormattedAttribute(): string
    {
        if (!$this->bitrate) {
            return '';
        }

        return $this->bitrate . ' kbps';
    }

    /**
     * Obtenir le nom du fichier sans extension
     */
    public function getFileNameWithoutExtensionAttribute(): string
    {
        if (!$this->file_name) {
            return '';
        }

        return pathinfo($this->file_name, PATHINFO_FILENAME);
    }

    /**
     * Obtenir l'extension du fichier
     */
    public function getFileExtensionAttribute(): string
    {
        if (!$this->file_name) {
            return '';
        }

        return strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
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

    /**
     * Vérifier si l'enregistrement est aujourd'hui
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->recorded_at && $this->recorded_at->isToday();
    }

    /**
     * Vérifier si l'enregistrement est récent (moins de 7 jours)
     */
    public function getIsRecentAttribute(): bool
    {
        return $this->created_at && $this->created_at->diffInDays(now()) <= 7;
    }

    /**
     * Obtenir l'URL du fichier
     */
    public function getFileUrlAttribute(): string
    {
        if (!$this->file_path) {
            return '';
        }

        return asset('storage/' . $this->file_path);
    }

    /**
     * Obtenir le type MIME
     */
    public function getMimeTypeAttribute(): string
    {
        $mimeTypes = [
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'm4a' => 'audio/mp4',
            'ogg' => 'audio/ogg'
        ];

        return $mimeTypes[$this->format] ?? 'audio/mpeg';
    }

    /**
     * Vérifier si le fichier peut être lu dans le navigateur
     */
    public function getIsBrowserCompatibleAttribute(): bool
    {
        $compatibleFormats = ['mp3', 'wav', 'ogg'];
        return in_array($this->format, $compatibleFormats);
    }
}
