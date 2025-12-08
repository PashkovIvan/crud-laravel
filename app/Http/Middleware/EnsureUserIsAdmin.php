<?php

namespace App\Http\Middleware;

use App\Domain\Common\Enums\ErrorMessage;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            Log::warning('Admin access denied', [
                'user_id' => $user?->id,
                'request_method' => $request->method(),
                'request_url' => $request->fullUrl(),
            ]);

            return response()->json([
                'message' => ErrorMessage::ADMIN_ACCESS_REQUIRED->value,
            ], 403);
        }

        return $next($request);
    }
}
