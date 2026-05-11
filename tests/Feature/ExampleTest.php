<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_route_is_registered(): void
    {
        $this->assertNotNull(Route::getRoutes()->getByName('dashboard-sales'));
    }

    public function test_router_matches_root_path(): void
    {
        $matched = Route::getRoutes()->match(Request::create('/', 'GET'));

        $this->assertSame('dashboard-sales', $matched->getName());
    }

    public function test_guest_is_redirected_from_home_to_filament_login(): void
    {
        $this->get('/')->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_authenticated_user_can_view_sales_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk();
    }
}
