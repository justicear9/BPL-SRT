<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_cannot_open_workspace_settings(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->get(route('workspace.settings.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_update_currency_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('workspace.settings.update'), [
                'currency_code' => 'EUR',
                'currency_symbol' => '€',
            ])
            ->assertRedirect(route('workspace.settings.edit'));

        $this->assertSame('EUR', Setting::currencyCode());
        $this->assertSame('€', Setting::currencySymbol());
    }

    public function test_admin_can_save_ghana_cedi_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('workspace.settings.update'), [
                'currency_code' => 'GHS',
                'currency_symbol' => '₵',
            ])
            ->assertRedirect(route('workspace.settings.edit'));

        $this->assertSame('GHS', Setting::currencyCode());
        $this->assertSame('₵', Setting::currencySymbol());
    }
}
