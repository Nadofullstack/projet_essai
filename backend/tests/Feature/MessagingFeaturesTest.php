<?php

namespace Tests\Feature;

use App\Models\Messages;
use App\Models\User;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class MessagingFeaturesTest extends TestCase
{
    /**
     * Test: Marquer un message comme nouveau et lire
     */
    public function test_message_is_marked_as_new_on_creation(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        Sanctum::actingAs($sender);

        $response = $this->postJson('/api/messages', [
            'content' => 'Test message',
            'type' => 'text',
            'receiver_id' => $receiver->id
        ]);

        $response->assertStatus(201);
        $this->assertTrue($response['data']['is_new']);
    }

    /**
     * Test: Indicateur visuel VERT pour nouveaux messages
     */
    public function test_new_messages_are_marked_green(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        // Créer un message comme nouveau
        $message = Messages::create([
            'content' => 'New message',
            'type' => 'text',
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'is_new' => true,
            'is_read' => false
        ]);

        Sanctum::actingAs($receiver);

        // Récupérer les messages du récepteur
        $response = $this->getJson('/api/messages');
        
        $newMessages = array_filter($response['data'], fn($m) => $m['is_new'] ?? false);
        $this->assertNotEmpty($newMessages);
    }

    /**
     * Test: Le statut utilisateur change à online quand connecté
     */
    public function test_user_status_updated_to_online(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/messages/user/status', [
            'online_status' => 'online'
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response['data']['online_status'] === 'online');
        
        // Vérifier en base de données
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'online_status' => 'online'
        ]);
    }

    /**
     * Test: Le timestamp last_seen_at est mis à jour
     */
    public function test_user_last_seen_at_updated(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $before = now();
        
        $this->postJson('/api/messages/user/status', [
            'online_status' => 'offline'
        ]);

        $after = now();

        $user->refresh();
        $this->assertTrue($user->last_seen_at->between($before, $after));
    }

    /**
     * Test: Affichage du statut d'autres utilisateurs
     */
    public function test_get_users_status(): void
    {
        $currentUser = User::factory()->create();
        $onlineUser = User::factory()->create(['online_status' => 'online']);
        $offlineUser = User::factory()->create(['online_status' => 'offline']);

        Sanctum::actingAs($currentUser);

        $response = $this->getJson('/api/messages/users/status');

        $response->assertStatus(200);
        $this->assertCount(3, $response['data']);
        
        $statuses = collect($response['data'])->keyBy('id');
        $this->assertEquals('online', $statuses[$onlineUser->id]['online_status']);
        $this->assertEquals('offline', $statuses[$offlineUser->id]['online_status']);
    }

    /**
     * Test: Supprimer tous les messages définitivement
     */
    public function test_delete_all_messages_permanently(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        // Créer plusieurs messages
        Messages::create([
            'content' => 'Message 1',
            'type' => 'text',
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id
        ]);

        Messages::create([
            'content' => 'Message 2',
            'type' => 'text',
            'sender_id' => $receiver->id,
            'receiver_id' => $sender->id
        ]);

        Sanctum::actingAs($sender);

        // Vérifier que les messages existent
        $this->assertCount(2, Messages::where('sender_id', $sender->id)->orWhere('receiver_id', $sender->id)->get());

        // Supprimer définitivement
        $response = $this->deleteJson('/api/messages/delete-all');

        $response->assertStatus(200);
        $this->assertTrue($response['success']);
        $this->assertEquals(2, $response['deleted_count']);

        // Vérifier que les messages sont supprimés (forceDelete)
        $this->assertCount(0, Messages::where('sender_id', $sender->id)->orWhere('receiver_id', $sender->id)->get());
    }

    /**
     * Test: Supprimer les anciens messages (plus de 30 jours)
     */
    public function test_delete_old_messages(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        // Créer un message ancien (plus de 30 jours)
        $oldMessage = Messages::create([
            'content' => 'Old message',
            'type' => 'text',
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'created_at' => now()->subDays(45)
        ]);

        // Créer un message récent
        $recentMessage = Messages::create([
            'content' => 'Recent message',
            'type' => 'text',
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'created_at' => now()->subDays(10)
        ]);

        Sanctum::actingAs($sender);

        // Supprimer les messages de plus de 30 jours
        $response = $this->deleteJson('/api/messages/delete-old', [
            'days_old' => 30,
            'confirm' => true
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response['success']);
        $this->assertEquals(1, $response['deleted_count']);

        // Vérifier que le message ancien est supprimé
        $this->assertNull(Messages::withoutTrashed()->find($oldMessage->id));

        // Vérifier que le message récent existe toujours
        $this->assertNotNull(Messages::find($recentMessage->id));
    }

    /**
     * Test: Valider la confirmation de suppression
     */
    public function test_delete_old_messages_requires_confirmation(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/messages/delete-old', [
            'days_old' => 30,
            'confirm' => false
        ]);

        $response->assertStatus(400);
        $this->assertFalse($response['success']);
    }

    /**
     * Test: Marquer un message comme lu et is_new = false
     */
    public function test_mark_message_as_read_sets_is_new_to_false(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $message = Messages::create([
            'content' => 'Test message',
            'type' => 'text',
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'is_new' => true,
            'is_read' => false
        ]);

        Sanctum::actingAs($receiver);

        $response = $this->putJson("/api/messages/{$message->id}/read");

        $response->assertStatus(200);

        $message->refresh();
        $this->assertTrue($message->is_read);
        $this->assertFalse($message->is_new);
    }

    /**
     * Test: Valider que seul le destinataire peut marquer un message comme lu
     */
    public function test_only_receiver_can_mark_message_as_read(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $otherUser = User::factory()->create();

        $message = Messages::create([
            'content' => 'Test message',
            'type' => 'text',
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/messages/{$message->id}/read");

        $response->assertStatus(403);
    }

    /**
     * Test: Obtenir les statistiques des messages
     */
    public function test_get_message_stats(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Créer plusieurs messages
        Messages::create([
            'content' => 'Message 1',
            'type' => 'text',
            'sender_id' => $user->id,
            'receiver_id' => $otherUser->id,
            'is_read' => false,
            'is_new' => true
        ]);

        Messages::create([
            'content' => 'Message 2',
            'type' => 'text',
            'sender_id' => $otherUser->id,
            'receiver_id' => $user->id,
            'is_read' => true,
            'is_new' => false
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/messages');

        $response->assertStatus(200);
        $this->assertCount(2, $response['data']);
        
        $unreadCount = collect($response['data'])->filter(fn($m) => !$m['is_read'])->count();
        $newCount = collect($response['data'])->filter(fn($m) => $m['is_new'] ?? false)->count();
        
        $this->assertEquals(1, $unreadCount);
        $this->assertEquals(1, $newCount);
    }

    /**
     * Test: Valider les permissions de suppression (soft delete)
     */
    public function test_delete_message_requires_ownership(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $otherUser = User::factory()->create();

        $message = Messages::create([
            'content' => 'Test message',
            'type' => 'text',
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/messages/{$message->id}");

        $response->assertStatus(403);
        $this->assertTrue(Messages::find($message->id) !== null);
    }
}
