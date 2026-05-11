<?php

namespace Tests\Feature;

use App\Enums\CustomerType;
use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceVisitCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    protected function emptyNestedVisitPayload(): array
    {
        return [
            'order_lines' => [['product_id' => '', 'quantity' => 1, 'unit_price' => '']],
            'samples' => [['product_id' => '', 'quantity' => 1]],
            'collections' => [['amount' => '', 'payment_method' => '', 'notes' => '']],
        ];
    }

    protected function makeContact(Customer $customer, string $name = 'Default Contact'): Contact
    {
        return Contact::query()->create([
            'customer_id' => $customer->id,
            'name' => $name,
            'phone' => '0540000001',
            'position' => 'Staff',
            'is_primary' => false,
        ]);
    }

    public function test_sales_rep_can_store_visit_with_nested_payload(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);
        $customer = Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::Pharmacy,
            'name' => 'Workspace Visit Customer',
        ]);
        $contact = $this->makeContact($customer);

        $product = Product::query()->create([
            'sku' => 'VIS-SKU',
            'name' => 'Visit line item',
            'default_unit_price' => 9.99,
            'can_be_sampled' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($rep)->post(route('workspace.visits.store'), [
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'comments' => 'Quick drop-in',
            'order_lines' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10],
            ],
            'samples' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            'collections' => [
                ['amount' => '12.50', 'payment_method' => 'cash', 'notes' => 'Morning pickup'],
            ],
        ]);

        $response->assertRedirect(route('workspace.visits.index'));
        $visit = Visit::query()->where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame($rep->id, $visit->user_id);
        $this->assertSame($contact->id, $visit->contact_id);
        $this->assertTrue($visit->order()->exists());
        $this->assertSame(1, $visit->samples()->count());
        $this->assertSame(1, $visit->collections()->count());
        $collection = $visit->collections()->firstOrFail();
        $this->assertSame('cash', $collection->payment_method);
    }

    public function test_collections_require_payment_method_when_amount_present(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);
        $customer = Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::Pharmacy,
            'name' => 'Payment Method Customer',
        ]);
        $contact = $this->makeContact($customer);

        $this->actingAs($rep)
            ->post(route('workspace.visits.store'), array_merge([
                'customer_id' => $customer->id,
                'contact_id' => $contact->id,
                'comments' => 'Test',
            ], $this->emptyNestedVisitPayload(), [
                'collections' => [
                    ['amount' => '10', 'payment_method' => '', 'notes' => ''],
                ],
            ]))
            ->assertSessionHasErrors(['collections.0.payment_method']);
    }

    public function test_manager_must_submit_owner_datetime_and_contact(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $rep = User::factory()->create(['role' => UserRole::SalesRep, 'email' => 'rep-visits@example.com']);
        $customer = Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::Hospital,
            'name' => 'Hospital A',
        ]);
        $contact = $this->makeContact($customer);

        $this->actingAs($manager)
            ->post(route('workspace.visits.store'), [
                'customer_id' => $customer->id,
            ])
            ->assertSessionHasErrors(['user_id', 'visited_at', 'contact_id']);

        $visitedAt = now()->subDay()->format('Y-m-d H:i:s');

        $this->actingAs($manager)
            ->post(route('workspace.visits.store'), array_merge([
                'customer_id' => $customer->id,
                'contact_id' => $contact->id,
                'user_id' => $rep->id,
                'visited_at' => $visitedAt,
            ], $this->emptyNestedVisitPayload()))
            ->assertRedirect(route('workspace.visits.index'));

        $visit = Visit::query()->where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame($rep->id, $visit->user_id);
        $this->assertSame($contact->id, $visit->contact_id);
    }

    public function test_sales_rep_can_update_own_visit(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);
        $customer = Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::Pharmacy,
            'name' => 'Edit Visit Customer',
        ]);
        $contact = $this->makeContact($customer);

        $visit = Visit::query()->create([
            'user_id' => $rep->id,
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'visited_at' => now()->subHours(2),
            'comments' => 'Before',
        ]);

        $this->actingAs($rep)
            ->put(route('workspace.visits.update', $visit), array_merge([
                'customer_id' => $customer->id,
                'contact_id' => $contact->id,
                'comments' => 'After',
            ], $this->emptyNestedVisitPayload()))
            ->assertRedirect(route('workspace.visits.index'));

        $this->assertSame('After', $visit->fresh()->comments);
    }

    public function test_store_visit_requires_contact_id(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);
        $customer = Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::Pharmacy,
            'name' => 'No Contact Visit Customer',
        ]);

        $this->actingAs($rep)
            ->post(route('workspace.visits.store'), array_merge([
                'customer_id' => $customer->id,
                'comments' => 'Missing contact',
            ], $this->emptyNestedVisitPayload()))
            ->assertSessionHasErrors(['contact_id']);
    }

    public function test_sales_rep_can_create_customer_contact_via_workspace_api(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);
        $customer = Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::Pharmacy,
            'name' => 'API Contact Customer',
        ]);

        $response = $this->actingAs($rep)->postJson(route('workspace.customers.contacts.store', $customer), [
            'name' => 'Jane Buyer',
            'phone' => '0547205377',
            'position' => 'Procurement',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['contact' => ['id', 'label']]);
        $this->assertStringContainsString('Jane Buyer', (string) $response->json('contact.label'));

        $this->assertDatabaseHas('contacts', [
            'customer_id' => $customer->id,
            'name' => 'Jane Buyer',
            'phone' => '0547205377',
            'position' => 'Procurement',
        ]);
    }

    public function test_store_visit_links_existing_contact(): void
    {
        $rep = User::factory()->create(['role' => UserRole::SalesRep]);
        $customer = Customer::query()->create([
            'assigned_user_id' => $rep->id,
            'type' => CustomerType::Pharmacy,
            'name' => 'Existing Contact Customer',
        ]);
        $contact = Contact::query()->create([
            'customer_id' => $customer->id,
            'name' => 'Sam Staff',
            'phone' => '0541234567',
            'position' => 'Manager',
            'is_primary' => false,
        ]);

        $this->actingAs($rep)->post(route('workspace.visits.store'), array_merge([
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'comments' => 'Routine',
        ], $this->emptyNestedVisitPayload()))->assertRedirect(route('workspace.visits.index'));

        $visit = Visit::query()->where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame($contact->id, $visit->contact_id);
    }
}
