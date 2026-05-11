<?php

namespace Tests\Feature;

use App\Enums\CustomerType;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_search_returns_json_for_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => UserRole::SalesRep]);

        $response = $this->actingAs($user)->getJson(route('navigation.search'));

        $response->assertOk();
        $response->assertJsonStructure(['navigation', 'suggestions', 'customers']);
        $this->assertIsArray($response->json('navigation'));
    }

    public function test_navigation_search_shortcuts_are_visits_and_customers_only(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($user)->getJson(route('navigation.search'));

        $response->assertOk();
        $response->assertJsonPath('suggestions.Shortcuts.0.name', __('Visits'));
        $response->assertJsonPath('suggestions.Shortcuts.0.icon', 'tabler-map-pin');
        $response->assertJsonPath('suggestions.Shortcuts.0.url', 'workspace/visits');
        $response->assertJsonPath('suggestions.Shortcuts.1.name', __('Customers'));
        $response->assertJsonPath('suggestions.Shortcuts.1.icon', 'tabler-building-store');
        $response->assertJsonPath('suggestions.Shortcuts.1.url', 'workspace/customers');
        $this->assertCount(2, $response->json('suggestions.Shortcuts'));
    }

    public function test_navigation_search_customers_are_scoped_for_sales_rep(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);
        $peer = User::factory()->create(['role' => UserRole::SalesRep]);

        Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::Pharmacy,
            'name' => 'Assigned Pharma',
        ]);
        Customer::query()->create([
            'assigned_user_id' => $peer->id,
            'type' => CustomerType::Hospital,
            'name' => 'Peer Hospital',
        ]);

        $response = $this->actingAs($rep)->getJson(route('navigation.search'));

        $response->assertOk();
        $customers = $response->json('customers');
        $this->assertCount(1, $customers);
        $this->assertSame('Assigned Pharma', $customers[0]['name']);
        $this->assertSame('Pharmacy', $customers[0]['subtitle']);
        $this->assertMatchesRegularExpression('#^workspace/customers/\d+/edit$#', $customers[0]['url']);
    }

    public function test_navigation_search_customers_include_all_for_manager(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);

        Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::Pharmacy,
            'name' => 'One',
        ]);
        Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::ChemicalShop,
            'name' => 'Two',
        ]);

        $response = $this->actingAs($manager)->getJson(route('navigation.search'));

        $response->assertOk();
        $this->assertCount(2, $response->json('customers'));
    }
}
