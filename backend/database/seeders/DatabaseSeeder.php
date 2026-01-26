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
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Créer quelques utilisateurs supplémentaires pour les tests
        User::factory()->create([
            'name' => 'Alice Dubois',
            'email' => 'alice@example.com',
            'password' => bcrypt('password123'),
        ]);

        User::factory()->create([
            'name' => 'Bob Martin',
            'email' => 'bob@example.com',
            'password' => bcrypt('password123'),
        ]);
    }
}
