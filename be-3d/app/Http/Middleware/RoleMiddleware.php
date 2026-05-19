<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $mapping = [
            'quan_tri' => 1,
            'quan_ly'  => 2,
            'khach_hang' => 3,
        ];

        // Chuyển đổi các role truyền vào từ tên sang số nếu cần
        $requiredRoles = array_map(fn($role) => $mapping[$role] ?? $role, $roles);

        if (!$request->user() || !in_array($request->user()->vai_tro, $requiredRoles)) {
            return response()->json([
                'message' => 'Bạn không có quyền thực hiện hành động này.',
            ], 403);
        }

        return $next($request);
    }
}
