<?php

use App\Http\Middleware\ResolveTenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::domain('{tenantSlug}.'.config('tenancy.central_domain'))
    ->middleware(ResolveTenant::class)
    ->group(function (): void {
        Route::get('/', function (
            Request $request,
            string $tenantSlug,
        ): JsonResponse {
            $tenant = $request->attributes->get('tenant');

            return response()->json([
                'company' => $tenant->name,
            ]);
        })->name('tenant.home');
    });

Route::domain(config('tenancy.central_domain'))
    ->group(function (): void {
        Route::get('/', function (): View {
            return view('welcome');
        })->name('home');
    });
