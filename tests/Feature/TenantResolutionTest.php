<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
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
}
