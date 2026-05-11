<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->dateTime('visited_at');
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index(['visited_at', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
