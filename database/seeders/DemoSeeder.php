<?php

namespace Database\Seeders;

use App\Enums\CustomerType;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitCollection;
use App\Models\VisitOrder;
use App\Models\VisitOrderLine;
use App\Models\VisitSample;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
            ]
        );
        $manager->forceFill([
            'role' => UserRole::Admin,
            'username' => 'admin',
        ])->save();

        $rep = User::query()->firstOrCreate(
            ['email' => 'rep@example.com'],
            [
                'name' => 'Demo Rep',
                'username' => 'rep',
                'password' => Hash::make('password'),
                'role' => UserRole::SalesRep,
            ]
        );
        $rep->forceFill(['username' => 'rep'])->save();

        $pharmacy = Customer::query()->firstOrCreate(
            ['name' => 'Demo Pharmacy'],
            [
                'type' => CustomerType::Pharmacy,
                'city' => 'Demo City',
                'phone' => '555-0100',
                'assigned_user_id' => $rep->id,
            ]
        );
        $pharmacy->forceFill(['assigned_user_id' => $rep->id])->save();
        $alex = $pharmacy->contacts()->firstOrCreate(
            ['name' => 'Alex Pharmacist'],
            [
                'is_primary' => true,
                'email' => 'alex@demo.pharmacy',
                'phone' => '0540000001',
                'position' => 'Lead Pharmacist',
            ]
        );

        $hospital = Customer::query()->firstOrCreate(
            ['name' => 'Demo Hospital'],
            [
                'type' => CustomerType::Hospital,
                'region' => 'North',
                'assigned_user_id' => $rep->id,
            ]
        );
        $hospital->forceFill(['assigned_user_id' => $rep->id])->save();

        $p1 = Product::query()->firstOrCreate(
            ['sku' => 'DEMO-001'],
            [
                'name' => 'Demo Product A',
                'default_unit_price' => 19.99,
                'can_be_sampled' => true,
                'is_active' => true,
            ]
        );
        $p2 = Product::query()->firstOrCreate(
            ['sku' => 'DEMO-002'],
            [
                'name' => 'Demo Product B',
                'default_unit_price' => 49.50,
                'can_be_sampled' => true,
                'is_active' => true,
            ]
        );

        $visit = Visit::query()->updateOrCreate(
            [
                'user_id' => $rep->id,
                'customer_id' => $pharmacy->id,
            ],
            [
                'visited_at' => now()->subDay()->startOfHour(),
                'comments' => 'Discussed restock; left samples.',
                'contact_id' => $alex->id,
            ],
        );

        if (! $visit->order) {
            $order = VisitOrder::query()->create(['visit_id' => $visit->id]);
            VisitOrderLine::query()->create([
                'visit_order_id' => $order->id,
                'product_id' => $p1->id,
                'quantity' => 2,
                'unit_price' => $p1->default_unit_price,
            ]);
        }

        if ($visit->samples()->doesntExist()) {
            VisitSample::query()->create([
                'visit_id' => $visit->id,
                'product_id' => $p2->id,
                'quantity' => 3,
            ]);
        }

        if ($visit->collections()->doesntExist()) {
            VisitCollection::query()->create([
                'visit_id' => $visit->id,
                'amount' => 120.00,
                'notes' => 'Partial payment on account',
            ]);
        }

        Visit::query()->updateOrCreate(
            [
                'user_id' => $rep->id,
                'customer_id' => $hospital->id,
            ],
            [
                'visited_at' => now()->subDays(3)->startOfHour(),
                'comments' => 'Follow-up scheduled.',
            ],
        );
    }
}
