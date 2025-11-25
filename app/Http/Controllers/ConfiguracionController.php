<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horario;        // modelo Horario
use App\Models\Clinica;        // modelo Clinica
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Support\ActivityLogger;
use App\Models\Servicio;

class ConfiguracionController extends Controller
{
    
     // Mostrar la vista de configuración con los horarios de la clínica del usuario.
     
    // Mostrar listado de clínicas para seleccionar
    public function index()
    {
        $usuario = Auth::user();
        $clinicas = Clinica::orderBy('nombre')->get();
        return view('configuracion_clinicas', compact('clinicas','usuario'));
    }

    // Mostrar vista de configuración para una clínica específica
    public function editarClinica(Request $request, Clinica $clinica)
    {
        $usuario = Auth::user();
        $clinicaId = $clinica->id;

        // Inicializar horarios si no existen
        $dias = ['lunes','martes','miércoles','jueves','viernes','sábado','domingo'];
        foreach ($dias as $dia) {
            Horario::firstOrCreate(
                [
                    'clinica_id' => $clinicaId,
                    'dia_semana' => $dia
                ],
                [
                    'hora_inicio' => '09:00',
                    'hora_fin' => '18:00',
                    'activo' => 1
                ]
            );
        }

        $ordenDias = "FIELD(dia_semana, 'lunes','martes','miércoles','jueves','viernes','sábado','domingo')";
        $horarios = Horario::where('clinica_id', $clinicaId)
            ->orderByRaw($ordenDias)
            ->get();

        // Trabajadores asociados a esta clínica
        $trabajadores = \App\Models\User::where('clinica_id', $clinicaId)
            ->whereIn('rol', ['A','R','V'])
            ->orderBy('nombre')
            ->get();

        // Filtro de búsqueda por email
        $searchEmail = trim($request->query('search_email', ''));

        // Trabajadores disponibles para asignar (admins y veterinarios no asignados a esta clínica), con filtro opcional por correo
        $availableTrabajadores = \App\Models\User::whereIn('rol', ['A','V'])
            ->where(function($q) use ($clinicaId) {
                $q->whereNull('clinica_id')->orWhere('clinica_id', '!=', $clinicaId);
            })
            ->when($searchEmail !== '', function($q) use ($searchEmail) {
                $q->where('email', 'like', '%' . $searchEmail . '%');
            })
            ->orderBy('nombre')
            ->get();

        return view('configuracion', [
            'clinica' => $clinica,
            'horarios' => $horarios,
            'usuario' => $usuario,
            'trabajadores' => $trabajadores,
            'availableTrabajadores' => $availableTrabajadores,
            'searchEmail' => $searchEmail,
        ]);
    }

    // Crear nueva clínica
    public function storeClinica(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:15',
            'email' => 'required|email|max:150|unique:clinicas,email',
            'site' => 'nullable|string|max:255',
            'is_open' => 'nullable|in:1',
            'is_visible' => 'nullable|in:1',
        ]);

        $clinica = Clinica::create([
            'nombre' => $data['nombre'],
            'direccion' => $data['direccion'],
            'telefono' => $data['telefono'],
            'email' => $data['email'],
            'site' => $data['site'] ?? null,
            'is_open' => isset($data['is_open']) ? 1 : 0,
            'is_visible' => isset($data['is_visible']) ? 1 : 0,
        ]);

        ActivityLogger::log($request, 'Crear clínica', 'Clinica', $clinica->id, [
            'clinica_id' => $clinica->id,
        ], Auth::id());

        return redirect()->route('configuracion.clinica', ['clinica' => $clinica->id, 'tab' => 'clinica'])
            ->with('success', 'Clínica creada correctamente.');
    }

    
     //Actualizar los horarios (recibe un array 'horarios' desde el formulario o JSON).
     
    public function updateHorarios(Request $request)
    {
        $clinicaId = (int)$request->input('clinica_id');
        $clinica = Clinica::find($clinicaId);
        if (!$clinica) {
            return redirect()->route('configuracion')->with('error', 'Clínica inválida.');
        }

        $payload = $request->input('horarios', []);
        if (!is_array($payload)) { $payload = []; }

        $errors = [];
        $updated = [];

        foreach ($payload as $id => $data) {
            if (!is_numeric($id)) { continue; }
            $horario = Horario::where('id', $id)->where('clinica_id', $clinicaId)->first();
            if (!$horario) { continue; }

            $horaInicio = $data['hora_inicio'] ?? null;
            $horaFin = $data['hora_fin'] ?? null;
            $activo = array_key_exists('activo', $data) ? 1 : 0; // checkbox ausente => inactivo

            $validator = Validator::make([
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
            ], [
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            ]);
            if ($validator->fails()) {
                $errors[$id] = $validator->errors()->all();
                continue;
            }

            // Ajustar a HH:MM:SS para columna time si se requiere
            $horario->hora_inicio = $horaInicio . ':00';
            $horario->hora_fin = $horaFin . ':00';
            if (isset($horario->activo)) { // solo si existe la columna
                $horario->activo = $activo;
            }
            $horario->save();
            $updated[] = $horario->id;
        }

        if ($errors) {
            return redirect()->route('configuracion.clinica', $clinicaId)
                ->with('error', 'Algunos horarios no se actualizaron.')
                ->with('horarios_errors', $errors);
        }

        // Log global de actualización de horarios
        if ($updated) {
            ActivityLogger::log($request, 'Actualizar horarios clínica', 'Clinica', $clinicaId, [
                'horarios_actualizados' => $updated,
            ], Auth::id());
        }

        return redirect()->route('configuracion.clinica', ['clinica' => $clinicaId, 'tab' => 'horario'])
            ->with('success', 'Horarios actualizados correctamente.');
    }

    // Actualizar información básica de la clínica
    public function updateClinicaInfo(Request $request, Clinica $clinica)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:15',
            'email' => 'required|email|max:150',
            'site' => 'nullable|string|max:255',
            'is_open' => 'nullable|in:0,1',
            'is_visible' => 'nullable|in:0,1',
        ]);

        // Normalizar flags (hidden input siempre envía 0, checkbox 1 si marcado)
        $data['is_open'] = isset($data['is_open']) ? (int)$data['is_open'] : $clinica->is_open;
        $data['is_visible'] = isset($data['is_visible']) ? (int)$data['is_visible'] : $clinica->is_visible;

        $original = $clinica->getOriginal();
        $clinica->update($data);

        // Determinar campos cambiados
        $changed = [];
        foreach ($data as $k => $v) {
            if (!array_key_exists($k, $original) || $original[$k] !== $v) {
                $changed[] = $k;
            }
        }

        // Log de actividad
        ActivityLogger::log($request, 'Actualizar clínica', 'Clinica', $clinica->id, [
            'changed_fields' => $changed,
        ], Auth::id());

        return redirect()->route('configuracion.clinica', ['clinica' => $clinica->id, 'tab' => 'clinica'])
            ->with('success', 'Información de la clínica actualizada correctamente.');
    }

    // Crear trabajador directamente desde la vista de configuración de la clínica
    public function storeTrabajadorClinica(Request $request, Clinica $clinica)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'fecha_nacimiento' => 'required|date',
            'rol' => 'required|in:A,R,V',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = \App\Models\User::create([
            'nombre' => $data['nombre'],
            'apellido_paterno' => $data['apellido_paterno'],
            'apellido_materno' => $data['apellido_materno'] ?? null,
            'email' => $data['email'],
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'rol' => $data['rol'],
            'genero' => 'O', // valor por defecto si no se solicita
            'telefono' => null,
            'imagen_perfil' => null,
            'is_active' => true,
            'clinica_id' => $clinica->id,
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
        ]);

        ActivityLogger::log($request, 'Crear trabajador clínica', 'User', $user->id, [
            'rol' => $user->rol,
            'clinica_id' => $clinica->id,
        ], Auth::id());

        return redirect()->route('configuracion.clinica', ['clinica' => $clinica->id, 'tab' => 'trabajadores'])
            ->with('success', 'Trabajador creado correctamente.');
    }

    // Asignar trabajador existente a la clínica
    public function asignarTrabajadorClinica(Request $request, Clinica $clinica)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = \App\Models\User::findOrFail($data['user_id']);

        // Solo permitir roles Admin o Veterinario
        if (!in_array($user->rol, ['A','V'])) {
            return redirect()->route('configuracion.clinica', $clinica->id)->with('error', 'El usuario no tiene un rol válido para ser asignado.');
        }

        $originalClinica = $user->clinica_id;
        $user->clinica_id = $clinica->id;
        $user->save();

        ActivityLogger::log($request, 'Asignar trabajador clínica', 'User', $user->id, [
            'rol' => $user->rol,
            'clinica_id_anterior' => $originalClinica,
            'clinica_id_nuevo' => $clinica->id,
        ], Auth::id());

        return redirect()->route('configuracion.clinica', ['clinica' => $clinica->id, 'tab' => 'trabajadores'])
            ->with('success', 'Trabajador asignado correctamente.');
    }

    // Remover (desasignar) trabajador de la clínica
    public function removerTrabajadorClinica(Request $request, Clinica $clinica, $userId)
    {
        $user = \App\Models\User::where('clinica_id', $clinica->id)->where('id', $userId)->first();
        if(!$user){
            return redirect()->route('configuracion.clinica', ['clinica' => $clinica->id, 'tab' => 'trabajadores'])
                ->with('error', 'Trabajador no encontrado en esta clínica.');
        }

        $user->clinica_id = null; // desasignar
        $user->save();

        ActivityLogger::log($request, 'Remover trabajador clínica', 'User', $user->id, [
            'clinica_id_removida' => $clinica->id,
            'rol' => $user->rol,
        ], Auth::id());

        return redirect()->route('configuracion.clinica', ['clinica' => $clinica->id, 'tab' => 'trabajadores'])
            ->with('success', 'Trabajador removido correctamente.');
    }

    // Agregar servicios predefinidos a una clínica (crea registros en tabla servicios con clinica_id)
    public function storeServiciosClinica(Request $request, Clinica $clinica)
    {
        $request->validate([
            'servicios' => 'required|array|min:1',
            'servicios.*' => 'string|max:150',
        ]);

        $presets = [
            'Consulta médica' => ['precio' => '0.00', 'tiempo' => 30, 'descripcion' => null],
            'Corte de pelo' => ['precio' => '0.00', 'tiempo' => 45, 'descripcion' => null],
            'Baño' => ['precio' => '0.00', 'tiempo' => 45, 'descripcion' => null],
            'Vacunación' => ['precio' => '0.00', 'tiempo' => 20, 'descripcion' => null],
            'Desparasitación' => ['precio' => '0.00', 'tiempo' => 20, 'descripcion' => null],
            'Limpieza dental' => ['precio' => '0.00', 'tiempo' => 60, 'descripcion' => null],
        ];

        $creados = [];
        foreach ($request->servicios as $nombre) {
            $nombre = trim($nombre);
            if ($nombre === '') { continue; }

            // Determinar defaults
            $def = $presets[$nombre] ?? ['precio' => '0.00', 'tiempo' => 30, 'descripcion' => null];

            // Evitar duplicados por nombre dentro de la misma clínica
            $exists = Servicio::where('clinica_id', $clinica->id)
                ->where('nombre', $nombre)
                ->exists();
            if ($exists) { continue; }

            $srv = Servicio::create([
                'clinica_id' => $clinica->id,
                'nombre' => $nombre,
                'descripcion' => $def['descripcion'],
                'precio' => $def['precio'],
                'tiempo_estimado' => $def['tiempo'],
            ]);
            $creados[] = $srv->id;
        }

        if (!empty($creados)) {
            ActivityLogger::log($request, 'Agregar servicios a clínica', 'Clinica', $clinica->id, [
                'servicios_creados' => $creados,
            ], Auth::id());
            return redirect()->route('configuracion')->with('success', 'Servicios agregados correctamente.');
        }

        return redirect()->route('configuracion')->with('error', 'No se agregaron servicios (posibles duplicados).');
    }

    // Listar servicios de una clínica (JSON)
    public function serviciosClinica(Request $request, Clinica $clinica)
    {
        $servicios = Servicio::where('clinica_id', $clinica->id)
            ->orderBy('nombre')
            ->get(['id','nombre','descripcion','precio','tiempo_estimado']);

        return response()->json([
            'clinica_id' => $clinica->id,
            'servicios' => $servicios,
        ]);
    }

    // Actualizar datos de un servicio (precio, tiempo, nombre opcional)
    public function updateServicio(Request $request, Servicio $servicio)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:150',
            'precio' => 'sometimes|required|numeric|min:0|max:9999999.99',
            'tiempo_estimado' => 'sometimes|required|integer|min:1|max:1440',
            'descripcion' => 'sometimes|nullable|string|max:255',
        ]);

        $original = $servicio->getOriginal();
        $servicio->fill($data);
        $servicio->save();

        // Determinar cambios
        $changed = [];
        foreach ($data as $k => $v) {
            if (!array_key_exists($k, $original) || $original[$k] != $v) {
                $changed[] = $k;
            }
        }

        ActivityLogger::log($request, 'Actualizar servicio clínica', 'Servicio', $servicio->id, [
            'clinica_id' => $servicio->clinica_id,
            'changed_fields' => $changed,
        ], Auth::id());

        return response()->json([
            'ok' => true,
            'servicio' => $servicio->only(['id','nombre','descripcion','precio','tiempo_estimado']),
        ]);
    }

    // Eliminar un servicio de una clínica
    public function eliminarServicio(Request $request, Servicio $servicio)
    {
        $id = $servicio->id;
        $clinicaId = $servicio->clinica_id;
        try {
            $servicio->delete();

            ActivityLogger::log($request, 'Eliminar servicio clínica', 'Servicio', $id, [
                'clinica_id' => $clinicaId,
            ], Auth::id());

            return response()->json(['ok' => true, 'id' => $id]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo eliminar el servicio.'
            ], 422);
        }
    }
}
