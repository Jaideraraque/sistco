<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Rol;

class SistcoController extends Controller
{
    // ─────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────
public function dashboard()
{
    $totalClientes   = Cliente::count();
    $clientesMorosos = Cliente::morosos()->count();
    $tasaMorosidad   = $totalClientes > 0 ? round(($clientesMorosos / $totalClientes) * 100, 2) : 0;
    $ingresoPromedio = Cliente::avg('mensualidad') ?? 0;

    $porMunicipio = Cliente::selectRaw('municipio, count(*) as total')
        ->groupBy('municipio')
        ->orderByDesc('total')
        ->get();

    // Niveles de riesgo ML desde BD
    $riesgoAlto  = Cliente::where('nivel_riesgo_ml', 'Alto')->count();
    $riesgoMedio = Cliente::where('nivel_riesgo_ml', 'Medio')->count();
    $riesgoBajo  = Cliente::where('nivel_riesgo_ml', 'Bajo')->count();

    // Proyecciones reales desde FastAPI
    $proyecciones = [];
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(5)
            ->get(env('ML_API_URL') . '/proyectar/ingresos');
        if ($response->ok()) {
            $proyecciones = $response->json('proyecciones') ?? [];
        }
    } catch (\Exception $e) {
        $proyecciones = [];
    }

    return view('sistco.dashboard', compact(
        'totalClientes', 'clientesMorosos', 'tasaMorosidad',
        'ingresoPromedio', 'porMunicipio', 'proyecciones',
        'riesgoAlto', 'riesgoMedio', 'riesgoBajo'
    ));
}

    // ─────────────────────────────────────────
    // ANÁLISIS
    // ─────────────────────────────────────────
public function analisis()
{
    $porMunicipio = Cliente::selectRaw('municipio, count(*) as total')
        ->groupBy('municipio')
        ->orderByDesc('total')
        ->get();

    $porMegas = Cliente::selectRaw('megas, count(*) as total')
        ->groupBy('megas')
        ->orderByDesc('total')
        ->get();

    $totalClientes   = Cliente::count();
    $clientesMorosos = Cliente::where('es_moroso', 1)->count();
    $tasaMorosidad   = $totalClientes > 0 ? round(($clientesMorosos / $totalClientes) * 100, 2) : 0;
    $ingresoPromedio = Cliente::avg('mensualidad') ?? 0;
    $antigüedadProm  = round(Cliente::avg('antiguedad_meses'), 1);

    // Niveles de riesgo ML
    $riesgoAlto  = Cliente::where('nivel_riesgo_ml', 'Alto')->count();
    $riesgoMedio = Cliente::where('nivel_riesgo_ml', 'Medio')->count();
    $riesgoBajo  = Cliente::where('nivel_riesgo_ml', 'Bajo')->count();

    // Distribución de antigüedad
    $antig0_12  = Cliente::whereBetween('antiguedad_meses', [0, 12])->count();
    $antig13_24 = Cliente::whereBetween('antiguedad_meses', [13, 24])->count();
    $antig25_48 = Cliente::whereBetween('antiguedad_meses', [25, 48])->count();
    $antig49_72 = Cliente::whereBetween('antiguedad_meses', [49, 72])->count();
    $antig72mas = Cliente::where('antiguedad_meses', '>', 72)->count();

    // Top municipios con mayor mora
    $municipiosMora = Cliente::selectRaw('municipio, ROUND(AVG(tasa_mora_historica)*100,2) as mora_prom, COUNT(*) as total')
        ->groupBy('municipio')
        ->orderByDesc('mora_prom')
        ->get();

    return view('sistco.analisis', compact(
        'porMunicipio', 'porMegas', 'totalClientes', 'clientesMorosos',
        'tasaMorosidad', 'ingresoPromedio', 'antigüedadProm',
        'riesgoAlto', 'riesgoMedio', 'riesgoBajo',
        'antig0_12', 'antig13_24', 'antig25_48', 'antig49_72', 'antig72mas',
        'municipiosMora'
    ));
}
    // ─────────────────────────────────────────
    // CARGA DE DATOS
    // ─────────────────────────────────────────
    public function cargaDatos()
    {
        $historial = \App\Models\CargaDatos::with('user')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('sistco.carga_datos', compact('historial'));
    }

    // ─────────────────────────────────────────
    // CLASIFICACIÓN DE CLIENTES (ML)
    // ─────────────────────────────────────────
    public function clasificacion(Request $request)
    {
        $kpis = [
            'total'     => Cliente::count(),
            'en_mora'   => Cliente::where('es_moroso', 1)->count(),
            'tasa_mora' => round(Cliente::avg('tasa_mora_historica') * 100, 2),
            'sin_mora'  => Cliente::where('es_moroso', 0)->count(),
            'alto'      => Cliente::where('nivel_riesgo_ml', 'Alto')->count(),
            'medio'     => Cliente::where('nivel_riesgo_ml', 'Medio')->count(),
            'bajo'      => Cliente::where('nivel_riesgo_ml', 'Bajo')->count(),
        ];

        $query = Cliente::query();

        if ($request->filled('riesgo')) {
            $query->where('nivel_riesgo_ml', $request->riesgo);
        }

        if ($request->filled('municipio')) {
            $query->where('municipio', $request->municipio);
        }

        if ($request->filled('buscar')) {
            $query->where('codigo_cliente', 'like', '%' . $request->buscar . '%');
        }

        $query->orderBy('probabilidad_ml', 'desc');

        $clientes   = $query->paginate(20)->withQueryString();
        $municipios = Cliente::distinct()->pluck('municipio')->sort()->values();

        return view('sistco.clasificacion', compact('clientes', 'kpis', 'municipios'));
    }

    // ─────────────────────────────────────────
    // INGRESOS
    // ─────────────────────────────────────────
    public function ingresos()
    {
        $ingresoMensual  = Cliente::sum('mensualidad');
        $ingresoPromedio = Cliente::avg('mensualidad');

        $proyecciones = [];
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->get(env('ML_API_URL') . '/proyectar/ingresos');

            if ($response->ok()) {
                $proyecciones = $response->json('proyecciones') ?? [];
            }
        } catch (\Exception $e) {
            $proyecciones = [];
        }

        return view('sistco.ingresos', compact(
            'ingresoMensual',
            'ingresoPromedio',
            'proyecciones'
        ));
    }

    // ─────────────────────────────────────────
    // SEGMENTACIÓN DE CLIENTES (ML)
    // ─────────────────────────────────────────
    public function segmentacion()
    {
        $segmentosBD = Cliente::selectRaw('
            municipio,
            count(*) as total,
            avg(mensualidad) as mensualidad_promedio,
            avg(tasa_mora_historica) as mora_promedio
        ')
        ->groupBy('municipio')
        ->orderByDesc('total')
        ->get();

        $segmentosML = [];
        $algoritmo   = 'K-Means (K=5)';
        $silhouette  = 0.4294;

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->get(env('ML_API_URL') . '/segmentos/resumen');

            if ($response->ok()) {
                $data        = $response->json();
                $segmentosML = $data['segmentos']        ?? [];
                $algoritmo   = $data['algoritmo']        ?? $algoritmo;
                $silhouette  = $data['silhouette_score'] ?? $silhouette;
            }
        } catch (\Exception $e) {
            $segmentosML = [];
        }

        return view('sistco.segmentacion', compact(
            'segmentosBD',
            'segmentosML',
            'algoritmo',
            'silhouette'
        ));
    }

    // ─────────────────────────────────────────
    // REPORTES
    // ─────────────────────────────────────────
    public function reportes()
    {
        $totalClientes   = Cliente::count();
        $clientesMorosos = Cliente::where('es_moroso', 1)->count();
        $tasaMorosidad   = $totalClientes > 0 ? round(($clientesMorosos / $totalClientes) * 100, 2) : 0;
        $ingresoTotal    = Cliente::sum(\DB::raw('mensualidad * 1000'));
    
        $porMunicipio = Cliente::selectRaw('
            municipio,
            count(*) as total,
            sum(mensualidad * 1000) as ingreso,
            avg(tasa_mora_historica) as mora_prom
        ')->groupBy('municipio')->orderByDesc('total')->get();
    
        $riesgoAlto  = Cliente::where('nivel_riesgo_ml', 'Alto')->count();
        $riesgoMedio = Cliente::where('nivel_riesgo_ml', 'Medio')->count();
        $riesgoBajo  = Cliente::where('nivel_riesgo_ml', 'Bajo')->count();
    
        $topRiesgo = Cliente::whereNotNull('probabilidad_ml')
            ->orderByDesc('probabilidad_ml')
            ->take(10)
            ->get();
    
        return view('sistco.reportes', compact(
            'totalClientes', 'clientesMorosos', 'tasaMorosidad',
            'ingresoTotal', 'porMunicipio', 'riesgoAlto', 'riesgoMedio',
            'riesgoBajo', 'topRiesgo'
        ));
    }

    // ─────────────────────────────────────────
    // ADMINISTRACIÓN
    // ─────────────────────────────────────────
    public function administracion()
    {
        $usuarios      = \App\Models\User::with('role')->get();
        $roles         = \App\Models\Rol::all();
        $totalClientes = Cliente::count();

        return view('sistco.administracion', compact(
            'usuarios',
            'roles',
            'totalClientes'
        ));
    }

    // ─────────────────────────────────────────
    // ASISTENTE IA
    // ─────────────────────────────────────────
    public function asistente()
{
    $historial = \App\Models\Conversacion::where('user_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->take(20)
        ->get()
        ->reverse()
        ->values();

    return view('sistco.asistente', compact('historial'));
}

public function asistenteConsulta(Request $request)
{
    $pregunta  = $request->input('pregunta', '');
    $historial = $request->input('historial', []);

    try {
        $response = \Illuminate\Support\Facades\Http::timeout(15)
            ->post(env('ML_API_URL') . '/asistente/consulta', [
                'pregunta'   => $pregunta,
                'user_id'    => auth()->id(),
                'session_id' => session()->getId(),
                'historial'  => $historial,
            ]);

        if ($response->ok()) {
            $data      = $response->json();
            $respuesta = $data['respuesta'] ?? 'No pude procesar tu pregunta.';
            $fuente    = $data['fuente']    ?? 'Groq';
        } else {
            $respuesta = 'El asistente no está disponible.';
            $fuente    = 'error';
        }

    } catch (\Exception $e) {
        $respuesta = 'No se pudo conectar con el asistente.';
        $fuente    = 'error';
    }

    // Guardar en BD
    \App\Models\Conversacion::create([
        'user_id'    => auth()->id(),
        'pregunta'   => $pregunta,
        'respuesta'  => $respuesta,
        'fuente'     => $fuente,
        'session_id' => session()->getId(),
    ]);

    return response()->json([
        'respuesta' => $respuesta,
        'fuente'    => $fuente,
    ]);
}

public function crearUsuario(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|min:8',
        'role_id'  => 'required|exists:roles,id',
    ]);

    \App\Models\User::create([
    'name'     => $request->name,
    'email'    => $request->email,
    'password' => bcrypt($request->password),
    'role_id'  => $request->role_id,
    'telefono' => $request->telefono,
    'activo'   => true,
    ]);

    return redirect()->route('administracion')
        ->with('success', 'Usuario creado correctamente.');
}

public function cambiarRol(Request $request, $id)
{
    $request->validate(['role_id' => 'required|exists:roles,id']);

    \App\Models\User::findOrFail($id)->update(['role_id' => $request->role_id]);

    return redirect()->route('administracion')
        ->with('success', 'Rol actualizado correctamente.');
}

public function toggleUsuario($id)
{
    $usuario = \App\Models\User::findOrFail($id);

    // No permitir desactivar al propio usuario
    if ($usuario->id === auth()->id()) {
        return redirect()->route('administracion')
            ->with('error', 'No puedes desactivar tu propia cuenta.');
    }

    $usuario->update(['activo' => !$usuario->activo]);

    $msg = $usuario->activo ? 'Usuario activado.' : 'Usuario desactivado.';
    return redirect()->route('administracion')->with('success', $msg);
}

public function eliminarUsuario($id)
{
    $usuario = \App\Models\User::findOrFail($id);

    if ($usuario->id === auth()->id()) {
        return redirect()->route('administracion')
            ->with('error', 'No puedes eliminar tu propia cuenta.');
    }

    $usuario->delete();
    return redirect()->route('administracion')
        ->with('success', 'Usuario eliminado correctamente.');
}
public function editarUsuario(Request $request, $id)
{
    $usuario = \App\Models\User::findOrFail($id);

    $request->validate([
        'name'  => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
    ]);

    $usuario->update([
    'name'     => $request->name,
    'email'    => $request->email,
    'telefono' => $request->telefono,
]);

    return redirect()->route('administracion')
        ->with('success', 'Usuario actualizado correctamente.');
}

public function actualizarPerfil(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email,' . auth()->id(),
        'cargo'    => 'nullable|string|max:100',
        'telefono' => 'nullable|string|max:20',
    ]);

    auth()->user()->update([
        'name'     => $request->name,
        'email'    => $request->email,
        'cargo'    => $request->cargo,
        'telefono' => $request->telefono,
    ]);

    return redirect()->route('profile.show')
        ->with('success', 'Perfil actualizado correctamente.');
}

public function actualizarFoto(Request $request)
{
    $usuario = auth()->user();

    // Eliminar foto
    if ($request->has('eliminar')) {
        if ($usuario->foto && file_exists(public_path('fotos/' . $usuario->foto))) {
            unlink(public_path('fotos/' . $usuario->foto));
        }
        $usuario->update(['foto' => null]);
        return redirect()->route('profile.show')
            ->with('success', 'Foto eliminada correctamente.');
    }

    // Subir foto nueva
    $request->validate([
        'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    if ($usuario->foto && file_exists(public_path('fotos/' . $usuario->foto))) {
        unlink(public_path('fotos/' . $usuario->foto));
    }

    $nombre = 'user_' . $usuario->id . '_' . time() . '.' . $request->foto->extension();
    $request->foto->move(public_path('fotos'), $nombre);
    $usuario->update(['foto' => $nombre]);

    return redirect()->route('profile.show')
        ->with('success', 'Foto actualizada correctamente.');
}

public function procesarCarga(Request $request)
{
    set_time_limit(600);

    $request->validate([
        'archivo' => 'required|file|mimes:xlsx,xls|max:51200',
    ]);

    $archivo = $request->file('archivo');
    $nombre  = $archivo->getClientOriginalName();

    try {
        // Enviar Excel a FastAPI para limpieza
        $response = \Illuminate\Support\Facades\Http::timeout(300)
            ->attach('archivo', file_get_contents($archivo->getRealPath()), $nombre)
            ->post(env('ML_API_URL') . '/procesar/excel');

        if (!$response->ok()) {
            throw new \Exception('FastAPI no pudo procesar el archivo. Código: ' . $response->status());
        }

        $data = $response->json();

        if (!$data || $data['status'] !== 'ok') {
            throw new \Exception($data['mensaje'] ?? 'Error en el procesamiento del Excel.');
        }

        $registros = $data['registros'];

        // Limpiar tabla e insertar en lotes
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('clientes')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach (array_chunk($registros, 100) as $lote) {
            \DB::table('clientes')->insert(array_map(function($r) {
                return [
                    'codigo_cliente'      => $r['codigo_cliente'],
                    'tipo_identificacion' => 'Cedula',
                    'mensualidad'         => $r['mensualidad'],
                    'megas'               => $r['megas'],
                    'municipio'           => $r['municipio'],
                    'metodo_pago'         => $r['metodo_pago'],
                    'fecha_instalacion'   => now()->subMonths((int)$r['antiguedad_meses'])->format('Y-m-d'),
                    'tasa_mora_historica' => $r['tasa_mora_historica'],
                    'n_moras_historicas'  => $r['n_moras_historicas'],
                    'n_meses_activos'     => $r['n_meses_activos'],
                    'antiguedad_meses'    => $r['antiguedad_meses'],
                    'es_moroso'           => $r['es_moroso'],
                    'dia_prom_pago_ult12' => $r['dia_prom_pago_ult12'],
                    'moras_ult_3_meses'   => $r['moras_ult_3_meses'],
                    'moras_ult_6_meses'   => $r['moras_ult_6_meses'],
                    'racha_limpia_final'  => $r['racha_limpia_final'],
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
            }, $lote));
        }

        $total = \App\Models\Cliente::count();

        // Lanzar predicciones en segundo plano
        $modelosEstado = 'pendiente';
        try {
            $check = \Illuminate\Support\Facades\Http::timeout(3)->get(env('ML_API_URL') . '/');
            if ($check->ok()) {
                $phpPath = 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe';
                $artisan  = base_path('artisan');
                pclose(popen("start /B \"\" \"{$phpPath}\" \"{$artisan}\" ml:calcular-predicciones", "r"));
                $modelosEstado = 'actualizados';
            }
        } catch (\Exception $e) {
            $modelosEstado = 'pendiente';
        }

        \App\Models\CargaDatos::create([
            'user_id'        => auth()->id(),
            'nombre_archivo' => $nombre,
            'total_clientes' => $total,
            'estado'         => 'exitoso',
            'modelos_estado' => $modelosEstado,
            'mensaje'        => "$total clientes importados y procesados correctamente.", // ← CAMBIADO
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => "✅ $total clientes importados y procesados correctamente. Modelos ML: $modelosEstado.", // ← CAMBIADO
        ]);

    } catch (\Exception $e) {
        \App\Models\CargaDatos::create([
            'user_id'        => auth()->id(),
            'nombre_archivo' => $nombre,
            'total_clientes' => 0,
            'estado'         => 'fallido',
            'modelos_estado' => 'sin cambios',
            'mensaje'        => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'error'   => $e->getMessage(),
        ]);
    }
}

public function exportarPDF()
{
    $totalClientes   = Cliente::count();
    $clientesMorosos = Cliente::where('es_moroso', 1)->count();
    $tasaMorosidad   = $totalClientes > 0 ? round(($clientesMorosos / $totalClientes) * 100, 2) : 0;
    $ingresoTotal    = Cliente::sum(\DB::raw('mensualidad * 1000'));
    $porMunicipio    = Cliente::selectRaw('municipio, count(*) as total, sum(mensualidad*1000) as ingreso')
        ->groupBy('municipio')->orderByDesc('total')->get();
    $riesgoAlto      = Cliente::where('nivel_riesgo_ml', 'Alto')->count();
    $riesgoMedio     = Cliente::where('nivel_riesgo_ml', 'Medio')->count();
    $riesgoBajo      = Cliente::where('nivel_riesgo_ml', 'Bajo')->count();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sistco.reportes_pdf', compact(
        'totalClientes', 'clientesMorosos', 'tasaMorosidad',
        'ingresoTotal', 'porMunicipio', 'riesgoAlto', 'riesgoMedio', 'riesgoBajo'
    ));

    $pdf->setPaper('A4', 'portrait');
    return $pdf->download('Reporte_SISTCO_' . now()->format('Y-m-d') . '.pdf');
}

public function exportarExcel()
{
    $clientes = Cliente::selectRaw('
        codigo_cliente, municipio, megas, mensualidad,
        antiguedad_meses, n_moras_historicas, tasa_mora_historica,
        es_moroso, nivel_riesgo_ml, probabilidad_ml
    ')->orderBy('municipio')->get();

    $nombreArchivo = 'Reporte_SISTCO_' . now()->format('Y-m-d') . '.csv';

    $cabecera = ['Código', 'Municipio', 'Megas', 'Mensualidad', 'Antigüedad (m)',
                 'Moras Históricas', 'Tasa Mora', 'Es Moroso', 'Nivel Riesgo ML', 'Probabilidad ML'];

    $callback = function() use ($clientes, $cabecera) {
        $handle = fopen('php://output', 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
        fputcsv($handle, $cabecera, ';');
        foreach ($clientes as $c) {
            fputcsv($handle, [
                $c->codigo_cliente,
                $c->municipio,
                $c->megas,
                $c->mensualidad * 1000,
                round($c->antiguedad_meses, 1),
                $c->n_moras_historicas,
                round($c->tasa_mora_historica * 100, 2) . '%',
                $c->es_moroso ? 'Sí' : 'No',
                $c->nivel_riesgo_ml ?? '—',
                $c->probabilidad_ml ? round($c->probabilidad_ml, 2) . '%' : '—',
            ], ';');
        }
        fclose($handle);
    };

    return response()->stream($callback, 200, [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$nombreArchivo\"",
    ]);
}

public function soporte()
{
    return view('soporte');
}

public function enviarSoporte(Request $request)
{
    $request->validate([
        'asunto'    => 'required|string|max:100',
        'prioridad' => 'required|in:alta,media,baja',
        'mensaje'   => 'required|string|max:2000',
    ]);

    $usuario = auth()->user();

    Mail::to(config('mail.from.address'))->send(new \App\Mail\SoporteMail(
        nombreUsuario: $usuario->name,
        correoUsuario: $usuario->email,
        rolUsuario:    $usuario->role?->nombre ?? 'Sin rol',
        asunto:        $request->asunto,
        prioridad:     $request->prioridad,
        mensaje:       $request->mensaje,
        paginaOrigen:  $request->headers->get('referer') ?? 'No disponible',
        fecha:         now()->format('d/m/Y H:i'),
    ));

    return back()->with('soporte_enviado', true);
}

public function soportePublico()
{
    return view('soporte-publico');
}

public function enviarSoportePublico(Request $request)
{
    $request->validate([
        'nombre'    => 'required|string|max:100',
        'correo'    => 'required|email|max:100',
        'asunto'    => 'required|string|max:100',
        'prioridad' => 'required|in:alta,media,baja',
        'mensaje'   => 'required|string|max:2000',
    ]);

    Mail::to(config('mail.from.address'))->send(new \App\Mail\SoporteMail(
        nombreUsuario: $request->nombre,
        correoUsuario: $request->correo,
        rolUsuario:    'No autenticado',
        asunto:        $request->asunto,
        prioridad:     $request->prioridad,
        mensaje:       $request->mensaje,
        paginaOrigen:  $request->headers->get('referer') ?? 'Página pública de soporte',
        fecha:         now()->format('d/m/Y H:i'),
    ));

    return back()->with('soporte_enviado', true);
}

}
