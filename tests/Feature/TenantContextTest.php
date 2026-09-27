<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\TenantContext;
use LogicException;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    public function test_it_rejects_access_without_a_selected_tenant(): void
    {
        $context = $this->app->make(TenantContext::class);

        $this->expectException(LogicException::class);

        $context->get();
    }

    public function test_it_returns_the_selected_tenant(): void
    {
        $tenant = Tenant::factory()->make();
        $context = $this->app->make(TenantContext::class);

        $context->set($tenant);

        $this->assertSame($tenant, $context->get());
    }

    public function test_clearing_removes_the_selected_tenant(): void
    {
        $tenant = Tenant::factory()->make();
        $context = $this->app->make(TenantContext::class);
        $context->set($tenant);

        $context->clear();

        $this->expectException(LogicException::class);

        $context->get();
    }
}
