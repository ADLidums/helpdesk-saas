<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TenantRelationshipTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_a_tenant_returns_only_its_own_users(): void
    {
        $potato = Tenant::factory()->create([
            'name' => 'Potato',
            'slug' => 'potato',
        ]);

        $tomato = Tenant::factory()->create([
            'name' => 'Tomato',
            'slug' => 'tomato',
        ]);

        $potatoUser = User::factory()->for($potato)->create();
        User::factory()->for($tomato)->create();

        $users = $potato->users;

        $this->assertSame([$potatoUser->id], $users->modelKeys());
    }

    public function test_a_user_belongs_to_its_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();

        $company = $user->tenant;

        $this->assertSame($tenant->id, $company->id);
    }
}
