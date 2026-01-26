<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Messages extends Model
{
    protected $fillable = [
        'content',
        'type', // text, image, file, audio
        'sender_id',
        'receiver_id',
        'has_attachment',
        'attachment_type',
        'attachment_path',
        'is_read',
        'parent_id' // pour les réponses
    ];

    protected $casts = [
        'has_attachment' => 'boolean',
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

        // protected $fillable = ['user_id', 'conversation_id', 'content', 'created_at'];

    protected $dispatchesEvents = [
        'created' => MessageSent::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
