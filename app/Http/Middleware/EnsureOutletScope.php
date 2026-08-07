<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOutletScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $outletId = $request->route('outletId');

        if ($user->outlet_id && $outletId && $user->outlet_id !== $outletId) {
            return response()->json([
                'message' => 'You do not have access to this outlet.',
            ], 403);
        }

        return $next($request);
    }
}