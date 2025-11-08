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
            'email' => 'admin2@example.com',
            'apellido_paterno' => 'Admin',
            'apellido_materno' => 'Admin',
            'rol' => 'A',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 'O',
            'telefono' => '3141930987',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'nombre'=> 'Veterinario',
            'email' => 'vet@example.com',
            'apellido_paterno' => 'Vet',
            'apellido_materno' => 'Vet',
            'rol' => 'V',
            'fecha_nacimiento' => '1992-03-01',
            'genero' => 'O',
            'telefono' => '3141610987',
            'password' => Hash::make('password'),
        ]);
    }
}