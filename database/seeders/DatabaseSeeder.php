<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::factory()->create([
            'nombre' => 'Admin',
            'email' => 'admin@example.com',
            'apellido_paterno' => 'Admin',
            'apellido_materno' => 'Admin',
            'rol' => 'A',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 'O',
            'telefono' => '3141930989',
            'password' => Hash::make('password'),
        ]);
    }
}
