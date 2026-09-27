<?php

use App\Http\Middleware\ResolveTenant;
use App\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::domain('{tenantSlug}.'.config('tenancy.central_domain'))
    ->middleware(ResolveTenant::class)
    ->group(function (): void {
        Route::get('/', function (
            TenantContext $tenantContext,
            string $tenantSlug,
        ): JsonResponse {
            return response()->json([
                'company' => $tenantContext->get()->name,
            ]);
        })->name('tenant.home');
    });

Route::domain(config('tenancy.central_domain'))
    ->group(function (): void {
        Route::get('/', function (): View {
            return view('welcome');
        })->name('home');
    });
