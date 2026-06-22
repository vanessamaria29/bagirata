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
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('trip_id')->nullable()->constrained('trips')->onDelete('cascade');
            $table->string('original_currency')->default('IDR');
            $table->decimal('exchange_rate', 15, 6)->default(1.0);
            $table->decimal('original_amount', 15, 2)->default(0.0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['trip_id']);
            $table->dropColumn(['trip_id', 'original_currency', 'exchange_rate', 'original_amount']);
        });
    }
};
