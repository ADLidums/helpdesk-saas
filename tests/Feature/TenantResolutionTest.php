<?php

namespace Tests\Feature;

use App\Http\Middleware\ResolveTenant;
use App\Models\Tenant;
use App\TenantContext;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Route;
use LogicException;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class TenantResolutionTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[TestWith(['potato', 'Potato'])]
    #[TestWith(['tomato', 'Tomato'])]
    public function test_subdomain_selects_the_matching_tenant(
        string $slug,
        string $companyName,
    ): void {
        Tenant::factory()->create([
            'name' => 'Potato',
            'slug' => 'potato',
        ]);

        Tenant::factory()->create([
            'name' => 'Tomato',
            'slug' => 'tomato',
        ]);

        $response = $this->get('http://'.$slug.'.localhost/');

        $response->assertOk();
        $response->assertExactJson([
            'company' => $companyName,
        ]);
    }

    public function test_an_unknown_subdomain_returns_not_found(): void
    {
        Tenant::factory()->create([
            'name' => 'Potato',
            'slug' => 'potato',
        ]);

        $response = $this->get('http://unknown.localhost/');

        $response->assertNotFound();
    }

    public function test_the_central_domain_does_not_require_a_tenant(): void
    {
        $response = $this->get('http://localhost/');

        $response->assertOk();
        $response->assertViewIs('welcome');
    }

    public function test_tenant_context_is_cleared_after_a_request(): void
    {
        Tenant::factory()->create([
            'name' => 'Potato',
            'slug' => 'potato',
        ]);

        $context = $this->app->make(TenantContext::class);

        $response = $this->get('http://potato.localhost/');

        $response->assertOk();

        $this->expectException(LogicException::class);

        $context->get();
    }

    public function test_tenant_context_is_cleared_when_a_route_throws(): void
    {
        Tenant::factory()->create([
            'name' => 'Potato',
            'slug' => 'potato',
        ]);

        $context = $this->app->make(TenantContext::class);

        Route::domain('{tenantSlug}.localhost')
            ->middleware(ResolveTenant::class)
            ->get('/test-failure', function (
                TenantContext $tenantContext,
                string $tenantSlug,
            ): never {
                throw new RuntimeException(
                    'Simulated failure for '.$tenantContext->get()->name,
                );
            });

        Exceptions::fake();

        $response = $this->getJson('http://potato.localhost/test-failure');

        $response->assertServerError();

        Exceptions::assertReported(
            fn (RuntimeException $exception): bool => $exception->getMessage() === 'Simulated failure for Potato',
        );

        $this->expectException(LogicException::class);

        $context->get();
    }
}
