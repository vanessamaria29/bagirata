<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Host [cite: 60]
            $table->string('title'); // Contoh: "Makan Siang di Senen"
            $table->string('location')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['active', 'settled'])->default('active'); // Settlement Tracker
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
