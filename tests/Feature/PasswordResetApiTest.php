<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResetPasswordCodeNotification;

it('envía un código de restablecimiento si el correo existe', function () {
    Notification::fake();

    $user = User::factory()->create(['password' => Hash::make('Secret123!')]);

    $response = $this->postJson('/api/password/forgot', [
        'email' => $user->email,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['message']);

    Notification::assertSentTo($user, ResetPasswordCodeNotification::class);

    $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();
    expect($record)->not()->toBeNull();
    expect(strlen($record->token))->toBe(6);
});

it('rechaza restablecimiento con código inválido', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/password/reset', [
        'email' => $user->email,
        'code' => '000000',
        'password' => 'NuevoPass123',
        'password_confirmation' => 'NuevoPass123',
    ]);

    $response->assertStatus(422);
});

it('permite restablecer contraseña con código válido', function () {
    $user = User::factory()->create(['password' => Hash::make('ViejoPass123')]);

    DB::table('password_reset_tokens')->insert([
        'email' => $user->email,
        'token' => '123456',
        'created_at' => now(),
    ]);

    $response = $this->postJson('/api/password/reset', [
        'email' => $user->email,
        'code' => '123456',
        'password' => 'NuevoPass123',
        'password_confirmation' => 'NuevoPass123',
    ]);

    $response->assertStatus(200)->assertJson(['message' => 'Contraseña restablecida correctamente']);

    $user->refresh();
    expect(Hash::check('NuevoPass123', $user->password))->toBeTrue();
    // Debe haber eliminado el código
    $exists = DB::table('password_reset_tokens')->where('email', $user->email)->exists();
    expect($exists)->toBeFalse();
});
