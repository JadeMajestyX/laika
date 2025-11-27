<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Mascota;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class VetReportesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_veterinarian_can_fetch_report_data(): void
    {
        $veterinario = User::factory()->create([
            'rol' => 'V',
        ]);

        $ownerOne = User::factory()->create(['rol' => 'U']);
        $ownerTwo = User::factory()->create(['rol' => 'U']);

        $clinica = Clinica::create([
            'nombre' => 'Clínica Central',
            'direccion' => 'Calle 123',
            'telefono' => '5551112223',
            'email' => 'central@example.com',
            'is_open' => true,
            'is_visible' => true,
            'site' => 'central.laika.test',
        ]);

        $servicioConsulta = Servicio::create([
            'clinica_id' => $clinica->id,
            'nombre' => 'Consulta',
            'descripcion' => 'Consulta general',
            'precio' => 450,
            'tiempo_estimado' => 30,
        ]);

        $servicioVacuna = Servicio::create([
            'clinica_id' => $clinica->id,
            'nombre' => 'Vacuna',
            'descripcion' => 'Aplicación de vacuna',
            'precio' => 600,
            'tiempo_estimado' => 20,
        ]);

        $mascotaPerro = Mascota::create([
            'user_id' => $ownerOne->id,
            'nombre' => 'Firulais',
            'especie' => 'Perro',
            'raza' => 'Mestizo',
            'imagen' => null,
            'fecha_nacimiento' => '2020-01-01',
            'sexo' => 'M',
            'peso' => 10,
            'notas' => 'Sano',
        ]);

        $mascotaGato = Mascota::create([
            'user_id' => $ownerTwo->id,
            'nombre' => 'Mishi',
            'especie' => 'Gato',
            'raza' => 'Siames',
            'imagen' => null,
            'fecha_nacimiento' => '2021-06-01',
            'sexo' => 'F',
            'peso' => 4,
            'notas' => 'Revisar',
        ]);

        Cita::create([
            'clinica_id' => $clinica->id,
            'servicio_id' => $servicioConsulta->id,
            'mascota_id' => $mascotaPerro->id,
            'creada_por' => $veterinario->id,
            'veterinario_id' => $veterinario->id,
            'fecha' => Carbon::now()->subDays(2),
            'notas' => 'Consulta mensual',
            'status' => 'completada',
            'tipo' => 'consulta',
        ]);

        Cita::create([
            'clinica_id' => $clinica->id,
            'servicio_id' => $servicioVacuna->id,
            'mascota_id' => $mascotaGato->id,
            'creada_por' => $veterinario->id,
            'veterinario_id' => $veterinario->id,
            'fecha' => Carbon::now()->subDay(),
            'notas' => 'Aplicación vacuna',
            'status' => 'completada',
            'tipo' => 'cita',
        ]);

        $response = $this->actingAs($veterinario)
            ->getJson('/vet-dashboard/data/reportes?periodo=este-mes');

        $response->assertOk()
            ->assertJsonPath('metricas.citas', 2)
            ->assertJsonPath('metricas.consultas', 1)
            ->assertJsonPath('metricas.mascotas', 2)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('mascotasAtendidas.fechas')
                    ->has('mascotasAtendidas.atendidas')
                    ->where('mascotasEspecie.Perro', 1)
                    ->where('mascotasEspecie.Gato', 1)
                    ->etc()
            );
    }
}
