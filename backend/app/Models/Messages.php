<?php

namespace App\Models;

use App\Events\MessageSent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Messages extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'content',
        'type', // text, image, file, audio
        'sender_id',
        'receiver_id',
        'has_attachment',
        'attachment_type',
        'attachment_path',
        'is_read',
        'is_new',
        'parent_id' // pour les réponses
    ];

    protected $casts = [
        'has_attachment' => 'boolean',
        'is_read' => 'boolean',
        'is_new' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**     * Dispatch l'événement MessageSent après la création d'un message
     */    protected static function booted()
    {
        static::created(function ($message) {
            event(new MessageSent($message));
        });
    }

     /**
     * Générer une clé de conversation unique pour deux utilisateurs
     * Peu importe qui est l'expéditeur ou le destinataire
     */    public function getConversationKeyAttribute(): string
    {
        $ids = [$this->sender_id, $this->receiver_id];
        sort($ids);
        return implode('_', $ids);
    }
 
    /**
     * Obtenir l'expéditeur du message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Obtenir le destinataire du message
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Obtenir le message parent (pour les réponses)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Messages::class, 'parent_id');
    }

    /**
     * Obtenir les réponses au message
     */
    public function replies()
    {
        return $this->hasMany(Messages::class, 'parent_id');
    }

    /**
     * Formater le contenu pour l'affichage
     */
    public function getShortContentAttribute(): string
    {
        $content = strip_tags($this->content);
        return strlen($content) > 100 ? substr($content, 0, 100) . '...' : $content;
    }

    /**
     * Obtenir le type formaté
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'text' => 'Texte',
            'image' => 'Image',
            'file' => 'Fichier',
            'audio' => 'Audio'
        ];
        return $labels[$this->type] ?? 'Texte';
    }
}
