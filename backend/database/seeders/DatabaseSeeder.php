<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer un utilisateur de test
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password123'),
            ]
        );
        
        // Créer un token pour cet utilisateur
        $testUser->tokens()->delete();
        $token = $testUser->createToken('test-token');
        echo "\n🔑 Token for test@example.com: " . $token->plainTextToken . "\n";

        // Créer quelques utilisateurs supplémentaires pour les tests
        User::firstOrCreate(
            ['email' => 'alice@example.com'],
            [
                'name' => 'Alice Dubois',
                'password' => bcrypt('password123'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'bob@example.com'],
            [
                'name' => 'Bob Martin',
                'password' => bcrypt('password123'),
            ]
        );
        
        echo "✅ Database seeding completed!\n";
    }
}
