<?php

namespace App\Events;

use App\Models\Messages;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Délai avant de diffuser l'événement
     */
    public int $delay = 0;

    public function __construct(
        public Messages $message
    ) {}

    /**
     * Obtenez les canaux sur lesquels l'événement doit être diffusé
     * Utilise un canal privé entre le sender et le receiver
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->getConversationKey()),
        ];
    }

    /**
     * Obtenir la clé unique de conversation
     * Assure une clé cohérente indépendamment de l'ordre des participants
     */
    private function getConversationKey(): string
    {
        $ids = [
            $this->message->sender_id,
            $this->message->receiver_id
        ];
        sort($ids);
        return implode('_', $ids);
    }

    /**
     * Nom de l'événement à diffuser
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Obtenez les données à diffuser avec l'événement
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'type' => $this->message->type,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'sender' => $this->message->sender ? [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
                'avatar' => $this->message->sender->profile_picture,
            ] : null,
            'has_attachment' => $this->message->has_attachment,
            'attachment_type' => $this->message->attachment_type,
            'attachment_path' => $this->message->attachment_path,
            'is_read' => $this->message->is_read,
            'created_at' => $this->message->created_at,
            'updated_at' => $this->message->updated_at,
        ];
    }
}