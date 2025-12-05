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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');

            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');

            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('variable_symbol')->unique();
            $table->string('payment_note')->unique()->nullable();

            $table->string('url_slug')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
