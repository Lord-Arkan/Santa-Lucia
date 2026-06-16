<?php

namespace App\Http\Middleware;

use App\Services\AppointmentExpirationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SyncExpiredAppointments
{
    public function __construct(private readonly AppointmentExpirationService $service)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->service->syncExpiredScheduledAppointments();

        return $next($request);
    }
}
