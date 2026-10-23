<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\UsuarioModel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!session()->has('idusuario')) {
            return redirect()->route('inicio')->with('error', 'Debe iniciar sesión para acceder al sistema.');
        }

        $usuario = UsuarioModel::find(session('idusuario'));
        if (!$usuario || strtolower($usuario->estado) !== 'activo') {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('inicio')->with('error', 'Su sesión ya no está activa. Inicie sesión nuevamente.');
        }

        $userRole = $this->normalizarRol($usuario->rol);

        if ($userRole === 'cliente') {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('inicio')->with('error', 'Esta cuenta no tiene acceso al panel interno.');
        }

        // La autorización se basa en la cuenta actual y no en datos antiguos de sesión.
        session(['rol' => $userRole, 'estado' => $usuario->estado, 'username' => $usuario->username]);

        if ($roles) {
            $rolesPermitidos = array_map(
                fn (string $rol) => $this->normalizarRol($rol),
                $roles
            );

            if (! in_array($userRole, $rolesPermitidos, true)) {
                return redirect()->route('empleado.dashboard')->with(
                    'error',
                    'No tiene permisos para acceder a esta sección.'
                );
            }
        }

        return $next($request);
    }

    /**
     * Convierte las variantes usadas en la base de datos a un nombre de rol único.
     */
    private function normalizarRol(?string $rol): string
    {
        return match (strtolower(trim((string) $rol))) {
            'admin', 'administrador' => 'administrador',
            'empleado', 'personal' => 'personal',
            'cliente' => 'cliente',
            default => strtolower(trim((string) $rol)),
        };
    }
}
