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
        Schema::create('venue_owners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->string('email', 100)->unique('venue_owners_email_unique')->nullable(false);
            $table->string('phone_number', 20)->unique('venue_owners_phone_number_unique')->nullable(false);
            $table->string('password', 100)->nullable(false);
            $table->string('national_id_number', 20)->unique('venue_owners_national_id_number_unique')->nullable(false);
            $table->string('token', 100)->unique('venue_owners_token_unique')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_owners');
    }
};
