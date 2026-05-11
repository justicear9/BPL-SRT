<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('assigned_user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->decimal('shop_latitude', 10, 7)->nullable()->after('region');
            $table->decimal('shop_longitude', 10, 7)->nullable()->after('shop_latitude');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->decimal('visit_latitude', 10, 7)->nullable()->after('comments');
            $table->decimal('visit_longitude', 10, 7)->nullable()->after('visit_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn(['visit_latitude', 'visit_longitude']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_user_id');
            $table->dropColumn(['shop_latitude', 'shop_longitude']);
        });
    }
};
