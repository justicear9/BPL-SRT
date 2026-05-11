<?php

namespace Tests\Feature;

use App\Enums\CustomerType;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WorkspaceCustomerImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_rep_cannot_open_import_form(): void
    {
        $user = User::factory()->create(['role' => UserRole::SalesRep]);

        $this->actingAs($user)
            ->get(route('workspace.customers.import'))
            ->assertForbidden();
    }

    public function test_manager_cannot_open_import_form(): void
    {
        $user = User::factory()->manager()->create();

        $this->actingAs($user)
            ->get(route('workspace.customers.import'))
            ->assertForbidden();
    }

    public function test_admin_can_open_import_form(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('workspace.customers.import'))
            ->assertOk()
            ->assertSee(__('Import customers'), false);
    }

    public function test_admin_can_import_customers_from_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $csv = "name,type,phone,city\nImported Alpha,pharmacy,111,Accra\nImported Beta,hospital,,\n";

        $file = UploadedFile::fake()->createWithContent('customers.csv', $csv);

        $response = $this->actingAs($admin)
            ->post(route('workspace.customers.import.store'), ['import' => $file]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(route('workspace.customers.import'));

        $this->assertSame(2, Customer::query()->count());
        $this->assertTrue(Customer::query()->where('name', 'Imported Alpha')->exists());
        $this->assertTrue(Customer::query()->where('name', 'Imported Beta')->exists());
    }

    public function test_admin_import_partial_success_reports_row_errors(): void
    {
        $admin = User::factory()->admin()->create();

        $csv = "name,type\nGood Row,pharmacy\nBad Row,not_a_real_type\n";

        $file = UploadedFile::fake()->createWithContent('mix.csv', $csv);

        $this->actingAs($admin)
            ->post(route('workspace.customers.import.store'), ['import' => $file])
            ->assertSessionHas('import_created', 1)
            ->assertSessionHas('import_errors');

        $this->assertSame(1, Customer::query()->where('name', 'Good Row')->count());
    }

    public function test_admin_import_creates_sales_rep_from_assignee_label_and_assigns_customers(): void
    {
        $admin = User::factory()->admin()->create();

        $csv = "name,type,assigned_user_email\nAlpha Pharma,pharmacy,Pat Smith\nBeta Shop,pharmacy,pat smith\n";

        $file = UploadedFile::fake()->createWithContent('customers.csv', $csv);

        $this->actingAs($admin)
            ->post(route('workspace.customers.import.store'), ['import' => $file])
            ->assertSessionHas('import_created', 2)
            ->assertSessionHas('import_users_created', 1);

        $rep = User::query()->where('username', 'pat_smith')->first();
        $this->assertNotNull($rep);
        $this->assertSame(UserRole::SalesRep, $rep->role);
        $this->assertSame(2, Customer::query()->where('assigned_user_id', $rep->id)->count());
    }

    public function test_admin_import_assignee_matches_existing_user_by_email(): void
    {
        $admin = User::factory()->admin()->create();
        $rep = User::factory()->create([
            'email' => 'field@example.com',
            'username' => 'fieldrep',
            'role' => UserRole::SalesRep,
        ]);

        $csv = "name,type,assigned_user_email\nLinked Row,pharmacy,field@example.com\n";

        $file = UploadedFile::fake()->createWithContent('customers.csv', $csv);

        $this->actingAs($admin)
            ->post(route('workspace.customers.import.store'), ['import' => $file])
            ->assertSessionHas('import_created', 1)
            ->assertSessionHas('import_users_created', 0);

        $customer = Customer::query()->where('name', 'Linked Row')->first();
        $this->assertNotNull($customer);
        $this->assertSame($rep->id, $customer->assigned_user_id);
    }

    public function test_admin_import_accepts_wholesale_retailer_type_alias(): void
    {
        $admin = User::factory()->admin()->create();

        $csv = "name,type\nWholesale Row,wholesale retailer\n";

        $file = UploadedFile::fake()->createWithContent('customers.csv', $csv);

        $this->actingAs($admin)
            ->post(route('workspace.customers.import.store'), ['import' => $file])
            ->assertSessionHas('import_created', 1);

        $customer = Customer::query()->where('name', 'Wholesale Row')->first();
        $this->assertNotNull($customer);
        $this->assertSame(CustomerType::Wholesaler, $customer->type);
    }
}
