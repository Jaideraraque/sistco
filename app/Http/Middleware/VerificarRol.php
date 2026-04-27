<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $usuario    = auth()->user();
        $rolUsuario = $usuario->role?->nombre ?? null;

        // Sin rol asignado — bloquear con mensaje claro
        if (!$rolUsuario) {
            abort(403, 'Tu cuenta no tiene un rol asignado. Contacta al administrador del sistema para que te asigne un rol antes de continuar.');
        }

        // Verificar si el rol está permitido para esta ruta
        if (!in_array($rolUsuario, $roles)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
        
        // Verificar si el usuario está activo
        if (!$usuario->activo) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta ha sido desactivada. Contacta al administrador del sistema.');
        }

        return $next($request);
    }
}