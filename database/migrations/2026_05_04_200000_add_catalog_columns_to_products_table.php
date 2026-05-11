<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('unit_of_measure', 32)->nullable()->after('name');
            $table->string('item_category_code', 32)->nullable()->after('unit_of_measure');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['unit_of_measure', 'item_category_code']);
        });
    }
};
