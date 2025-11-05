<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->optional()->lastName(),
            'rol' => fake()->randomElement(['U','A','R','V']),
            'fecha_nacimiento' => fake()->date('Y-m-d', '-18 years'),
            'genero' => fake()->randomElement(['M','F','O']),
            'email' => fake()->unique()->safeEmail(),
            'telefono' => fake()->optional()->numerify('3#########'),
            'imagen_perfil' => null,
            'is_active' => true,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            // Si se llegara a usar un campo de verificación en el futuro,
            // aquí se podría ajustar el estado correspondiente.
        ]);
    }
}
