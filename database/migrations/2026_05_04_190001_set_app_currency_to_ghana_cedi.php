<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('settings')->upsert(
            [
                [
                    'key' => Setting::KEY_CURRENCY_SYMBOL,
                    'value' => '₵',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => Setting::KEY_CURRENCY_CODE,
                    'value' => 'GHS',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['key'],
            ['value', 'updated_at']
        );
    }

    public function down(): void
    {
        $now = now();

        DB::table('settings')->upsert(
            [
                [
                    'key' => Setting::KEY_CURRENCY_SYMBOL,
                    'value' => '$',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => Setting::KEY_CURRENCY_CODE,
                    'value' => 'USD',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['key'],
            ['value', 'updated_at']
        );
    }
};
