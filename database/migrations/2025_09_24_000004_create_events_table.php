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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('seats_total');
            $table->string('title');
            $table->string('url_slug')->unique();
            $table->dateTime('start_time');
            $table->dateTime('registration_start');
            $table->dateTime('registration_end');
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->string('contact_name');
            $table->string('bank_account');
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->boolean('multiple_reservations_per_ticket')->default(false);
            $table->text('description')->nullable();
            $table->json('additional_information')->nullable();
            $table->string('background_image_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
