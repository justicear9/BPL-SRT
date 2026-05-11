<?php

namespace Tests\Feature;

use App\Enums\CustomerType;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use App\Models\Visit;
use App\Services\DashboardWeekVisitMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardWeekVisitMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_week_map_service_is_null_for_non_admin(): void
    {
        $user = User::factory()->manager()->create();

        $this->assertNull(DashboardWeekVisitMap::forUser($user));
    }

    public function test_admin_dashboard_includes_week_visit_map_when_visits_have_gps(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-06 14:00:00', config('app.timezone')));

        $rep = User::factory()->create(['role' => UserRole::SalesRep]);
        $admin = User::factory()->admin()->create();
        $customer = Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::Pharmacy,
            'name' => 'Map Test Pharmacy',
        ]);

        Visit::query()->create([
            'user_id' => $rep->id,
            'customer_id' => $customer->id,
            'visited_at' => Carbon::parse('2026-05-05 10:00:00', config('app.timezone')),
            'comments' => null,
            'visit_latitude' => 5.6037,
            'visit_longitude' => -0.1870,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard-sales'))
            ->assertOk()
            ->assertSee('salesWeekVisitMap', false)
            ->assertSee('Visit locations', false);

        Carbon::setTestNow();
    }

    public function test_sales_rep_dashboard_does_not_include_admin_week_map(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);

        $this->actingAs($rep)
            ->get(route('dashboard-sales'))
            ->assertOk()
            ->assertDontSee('salesWeekVisitMap', false);
    }
}
