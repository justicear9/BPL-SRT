<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('position')->nullable()->after('phone');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('contact_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contact_id');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
