<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\SalesSidebarMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesSidebarMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_rep_menu_has_visits_but_not_products_or_orders(): void
    {
        $user = User::factory()->create(['role' => UserRole::SalesRep]);
        $this->actingAs($user);

        $menu = app(SalesSidebarMenu::class)->forUser($user);
        $labels = array_map(fn ($row) => $row['name'], $menu);

        $this->assertContains('Dashboard', $labels);
        $this->assertContains('Customers', $labels);
        $this->assertContains('Visits', $labels);
        $this->assertContains('Sales reps', $labels);
        $this->assertContains('Reports', $labels);
        $this->assertNotContains('Products', $labels);
        $this->assertNotContains('Orders', $labels);
    }

    public function test_sales_reps_group_appears_before_reports_for_rep(): void
    {
        $user = User::factory()->create(['role' => UserRole::SalesRep]);
        $this->actingAs($user);

        $menu = app(SalesSidebarMenu::class)->forUser($user);
        $labels = array_map(fn ($row) => $row['name'], $menu);

        $idxSalesReps = array_search('Sales reps', $labels, true);
        $idxReports = array_search('Reports', $labels, true);

        $this->assertNotFalse($idxSalesReps);
        $this->assertNotFalse($idxReports);
        $this->assertLessThan($idxReports, $idxSalesReps);
    }

    public function test_sales_reps_group_appears_before_reports_for_staff(): void
    {
        $user = User::factory()->create(['role' => UserRole::Manager]);
        $this->actingAs($user);

        $menu = app(SalesSidebarMenu::class)->forUser($user);
        $labels = array_map(fn ($row) => $row['name'], $menu);

        $idxSalesReps = array_search('Sales reps', $labels, true);
        $idxReports = array_search('Reports', $labels, true);

        $this->assertNotFalse($idxSalesReps);
        $this->assertNotFalse($idxReports);
        $this->assertLessThan($idxReports, $idxSalesReps);
    }

    public function test_manager_menu_includes_products_and_orders(): void
    {
        $user = User::factory()->create(['role' => UserRole::Manager]);
        $this->actingAs($user);

        $menu = app(SalesSidebarMenu::class)->forUser($user);
        $labels = array_map(fn ($row) => $row['name'], $menu);

        $this->assertContains('Products', $labels);
        $this->assertContains('Orders', $labels);
        $this->assertContains('Visits', $labels);
        $this->assertNotContains('Settings', $labels);
    }

    public function test_admin_menu_includes_settings_link(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $menu = app(SalesSidebarMenu::class)->forUser($user);
        $labels = array_map(fn ($row) => $row['name'], $menu);

        $this->assertContains('Settings', $labels);
    }
}
