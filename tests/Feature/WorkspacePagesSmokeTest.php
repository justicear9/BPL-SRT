<?php

namespace Tests\Feature;

use App\Enums\CustomerType;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspacePagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_workspace_list_and_form_pages_render(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        Customer::query()->create([
            'assigned_user_id' => $manager->id,
            'type' => CustomerType::Pharmacy,
            'name' => 'Acme Pharmacy',
        ]);

        $this->actingAs($manager);

        $product = Product::query()->create([
            'sku' => 'SMOKE-SKU',
            'name' => 'Smoke product',
            'default_unit_price' => 12.5,
            'can_be_sampled' => true,
            'is_active' => true,
        ]);

        foreach ([
            route('workspace.users.index'),
            route('workspace.users.create'),
            route('workspace.customers.index'),
            route('workspace.customers.create'),
            route('workspace.products.index'),
            route('workspace.products.create'),
            route('workspace.products.edit', $product),
            route('workspace.orders.index'),
            route('workspace.visits.index'),
            route('workspace.visits.create'),
            route('reports.visits'),
            route('reports.orders'),
            route('reports.samples'),
            route('reports.alpha'),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $other = User::factory()->create(['role' => UserRole::SalesRep, 'email' => 'other@example.com']);
        $this->get(route('workspace.users.edit', $other))->assertOk();

        $customer = Customer::query()->firstOrFail();
        $this->get(route('workspace.customers.edit', $customer))->assertOk();
    }

    public function test_sales_rep_workspace_pages_render(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);
        Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::Hospital,
            'name' => 'City Hospital',
        ]);

        $this->actingAs($rep);

        foreach ([
            route('dashboard-sales'),
            route('workspace.customers.index'),
            route('workspace.visits.index'),
            route('workspace.visits.create'),
            route('workspace.profile.edit'),
            route('reports.visits'),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $customer = Customer::query()->firstOrFail();
        $visit = Visit::query()->create([
            'user_id' => $rep->id,
            'customer_id' => $customer->id,
            'visited_at' => now(),
        ]);
        $this->get(route('workspace.visits.edit', $visit))->assertOk();
    }

    public function test_workspace_visit_create_and_edit_render_for_manager(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        Customer::query()->create([
            'assigned_user_id' => $manager->id,
            'type' => CustomerType::Pharmacy,
            'name' => 'Manager Visit Customer',
        ]);

        $this->actingAs($manager)
            ->get(route('workspace.visits.create'))
            ->assertOk();

        $customer = Customer::query()->where('name', 'Manager Visit Customer')->firstOrFail();
        $visit = Visit::query()->create([
            'user_id' => $manager->id,
            'customer_id' => $customer->id,
            'visited_at' => now(),
        ]);
        $this->get(route('workspace.visits.edit', $visit))->assertOk();
    }
}
