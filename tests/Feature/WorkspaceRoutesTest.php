<?php

namespace Tests\Feature;

use App\Enums\CustomerType;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_rep_cannot_list_workspace_users(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);

        $this->actingAs($rep)
            ->get(route('workspace.users.index'))
            ->assertForbidden();
    }

    public function test_sales_rep_can_open_profile_workspace(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);

        $this->actingAs($rep)
            ->get(route('workspace.profile.edit'))
            ->assertOk();
    }

    public function test_manager_can_list_workspace_users(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);

        $this->actingAs($manager)
            ->get(route('workspace.users.index'))
            ->assertOk();
    }

    public function test_sales_rep_cannot_list_workspace_orders(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);

        $this->actingAs($rep)
            ->get(route('workspace.orders.index'))
            ->assertForbidden();
    }

    public function test_manager_can_list_workspace_orders(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);

        $this->actingAs($manager)
            ->get(route('workspace.orders.index'))
            ->assertOk();
    }

    public function test_manager_can_open_visit_modal_fragment(): void
    {
        $manager = User::factory()->manager()->create();
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);
        $customer = Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::Pharmacy,
            'name' => 'Modal Visit Customer',
        ]);
        $visit = Visit::query()->create([
            'user_id' => $rep->id,
            'customer_id' => $customer->id,
            'visited_at' => now(),
            'comments' => null,
        ]);

        $this->actingAs($manager)
            ->get(route('workspace.visits.modal', ['visit' => $visit]))
            ->assertOk()
            ->assertSee('Modal Visit Customer', false)
            ->assertSee(__('Summary only'), false);
    }

    public function test_sales_rep_can_list_customers_workspace(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);
        Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::Pharmacy,
            'name' => 'Test Shop',
        ]);

        $this->actingAs($rep)
            ->get(route('workspace.customers.index'))
            ->assertOk();
    }

    public function test_sales_rep_cannot_create_customer_workspace(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);

        $this->actingAs($rep)
            ->get(route('workspace.customers.create'))
            ->assertForbidden();
    }

    public function test_manager_can_promote_sales_rep_to_manager(): void
    {
        $manager = User::factory()->manager()->create();
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);

        $this->actingAs($manager)
            ->put(route('workspace.users.update', $rep), [
                'name' => $rep->name,
                'username' => $rep->username,
                'email' => $rep->email,
                'password' => '',
                'password_confirmation' => '',
                'role' => UserRole::Manager->value,
            ])
            ->assertRedirect(route('workspace.users.index'));

        $this->assertSame(UserRole::Manager, $rep->fresh()->role);
    }
}
