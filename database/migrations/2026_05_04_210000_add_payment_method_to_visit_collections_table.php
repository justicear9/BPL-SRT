<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visit_collections', function (Blueprint $table): void {
            $table->string('payment_method', 32)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('visit_collections', function (Blueprint $table): void {
            $table->dropColumn('payment_method');
        });
    }
};
