<?php

namespace App\Http\Responses;

use App\Support\ModuleCatalog;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $routeName = ModuleCatalog::firstAccessibleRoute($request->user());

        return redirect()->route($routeName ?? 'dashboard');
    }
}
