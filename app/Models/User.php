<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Testing\Fluent\Concerns\Has;
use Laravel\Sanctum\HasApiTokens;
    use App\Notifications\ResetPasswordNotification as CustomResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'rol',
        'fecha_nacimiento',
        'genero',
        'email',
        'telefono',
        'imagen_perfil',
        'is_active',
        'clinica_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // app/Models/User.php

    public function mascotas()
    {
        return $this->hasMany(Mascota::class);
    }

        public function hasRole($rol)
    {
        return $this->rol === $rol; // si solo tienes un campo 'rol' en la tabla users
    }



public function sendPasswordResetNotification($token)
{
    $this->notify(new CustomResetPasswordNotification($token));
}

public function clinica()
{
    return $this->belongsTo(\App\Models\Clinica::class, 'clinica_id');
}
}
