<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\Messages;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MessageBroadcastingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer deux utilisateurs de test
        $this->user1 = User::factory()->create(['id' => 1]);
        $this->user2 = User::factory()->create(['id' => 2]);
    }

    /**
     * Tester que l'événement MessageSent est dispatché quand un message est créé
     */
    public function test_message_sent_event_is_dispatched_on_create()
    {
        Event::fake();

        $message = Messages::create([
            'content' => 'Test message',
            'type' => 'text',
            'sender_id' => $this->user1->id,
            'receiver_id' => $this->user2->id,
            'is_read' => false,
        ]);

        Event::assertDispatched(MessageSent::class, function ($event) {
            return $event->message->id === 1;
        });
    }

    /**
     * Tester la clé de conversation
     * Elle doit être la même peu importe l'ordre des participants
     */
    public function test_conversation_key_is_consistent()
    {
        $message1 = Messages::create([
            'content' => 'Message de 1 à 2',
            'type' => 'text',
            'sender_id' => $this->user1->id,
            'receiver_id' => $this->user2->id,
        ]);

        $message2 = Messages::create([
            'content' => 'Message de 2 à 1',
            'type' => 'text',
            'sender_id' => $this->user2->id,
            'receiver_id' => $this->user1->id,
        ]);

        // Créer une instance de MessageSent pour tester la clé
        $event1 = new MessageSent($message1);
        $event2 = new MessageSent($message2);

        // Utiliser la réflexion pour accéder à la méthode privée
        $reflection1 = new \ReflectionMethod($event1, 'getConversationKey');
        $reflection1->setAccessible(true);

        $reflection2 = new \ReflectionMethod($event2, 'getConversationKey');
        $reflection2->setAccessible(true);

        $key1 = $reflection1->invoke($event1);
        $key2 = $reflection2->invoke($event2);

        // Les clés doivent être identiques
        $this->assertEquals($key1, $key2);
        $this->assertEquals('1_2', $key1);
    }

    /**
     * Tester la structure des données diffusées
     */
    public function test_broadcast_data_structure()
    {
        $message = Messages::create([
            'content' => 'Test message',
            'type' => 'text',
            'sender_id' => $this->user1->id,
            'receiver_id' => $this->user2->id,
            'has_attachment' => false,
            'is_read' => false,
        ]);

        // Charger les relations
        $message->load(['sender', 'receiver']);

        $event = new MessageSent($message);
        $broadcastData = $event->broadcastWith();

        $this->assertIsArray($broadcastData);
        $this->assertArrayHasKey('id', $broadcastData);
        $this->assertArrayHasKey('content', $broadcastData);
        $this->assertArrayHasKey('sender_id', $broadcastData);
        $this->assertArrayHasKey('receiver_id', $broadcastData);
        $this->assertArrayHasKey('sender', $broadcastData);
        $this->assertArrayHasKey('created_at', $broadcastData);
        $this->assertArrayHasKey('is_read', $broadcastData);

        $this->assertEquals('Test message', $broadcastData['content']);
        $this->assertEquals($this->user1->id, $broadcastData['sender_id']);
        $this->assertEquals($this->user2->id, $broadcastData['receiver_id']);
    }

    /**
     * Tester l'endpoint API pour envoyer un message
     */
    public function test_send_message_via_api()
    {
        $this->actingAs($this->user1, 'sanctum');

        $response = $this->postJson('/api/messages', [
            'content' => 'Hello via API',
            'type' => 'text',
            'receiver_id' => $this->user2->id,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'content',
                'type',
                'sender_id',
                'receiver_id',
                'created_at',
            ],
        ]);

        // Vérifier que le message a été créé
        $this->assertDatabaseHas('messages', [
            'content' => 'Hello via API',
            'sender_id' => $this->user1->id,
            'receiver_id' => $this->user2->id,
        ]);
    }

    /**
     * Tester le marquage d'un message comme lu
     */
    public function test_mark_message_as_read()
    {
        $message = Messages::create([
            'content' => 'Unread message',
            'type' => 'text',
            'sender_id' => $this->user1->id,
            'receiver_id' => $this->user2->id,
            'is_read' => false,
        ]);

        $this->actingAs($this->user2, 'sanctum');

        $response = $this->putJson("/api/messages/{$message->id}/read");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertTrue($message->fresh()->is_read);
    }

    /**
     * Tester la liste des conversations
     */
    public function test_get_conversations()
    {
        // Créer plusieurs messages
        Messages::create([
            'content' => 'Message 1',
            'type' => 'text',
            'sender_id' => $this->user1->id,
            'receiver_id' => $this->user2->id,
        ]);

        Messages::create([
            'content' => 'Message 2',
            'type' => 'text',
            'sender_id' => $this->user2->id,
            'receiver_id' => $this->user1->id,
        ]);

        $this->actingAs($this->user1, 'sanctum');

        $response = $this->getJson('/api/messages/conversations');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'user',
                    'last_message',
                    'unread_count',
                ],
            ],
        ]);
    }

    /**
     * Tester les messages non lus
     */
    public function test_get_unread_messages()
    {
        Messages::create([
            'content' => 'Unread 1',
            'type' => 'text',
            'sender_id' => $this->user1->id,
            'receiver_id' => $this->user2->id,
            'is_read' => false,
        ]);

        Messages::create([
            'content' => 'Unread 2',
            'type' => 'text',
            'sender_id' => $this->user1->id,
            'receiver_id' => $this->user2->id,
            'is_read' => false,
        ]);

        $this->actingAs($this->user2, 'sanctum');

        $response = $this->getJson('/api/messages/unread');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.unread_count', 2);
    }

    /**
     * Tester que les utilisateurs ne peuvent lire que leurs propres messages
     */
    public function test_unauthorized_user_cannot_mark_message_as_read()
    {
        $message = Messages::create([
            'content' => 'Message from user1 to user2',
            'type' => 'text',
            'sender_id' => $this->user1->id,
            'receiver_id' => $this->user2->id,
            'is_read' => false,
        ]);

        // user1 ne peut pas marquer le message comme lu (lui c'est le sender)
        $this->actingAs($this->user1, 'sanctum');

        $response = $this->putJson("/api/messages/{$message->id}/read");

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }
}
