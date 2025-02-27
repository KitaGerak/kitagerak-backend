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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->unsignedBigInteger('login_method_id')->default(1);
            $table->string('phone_number')->nullable();
            $table->string('photo_url')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->double('balance')->default(0);
            $table->unsignedBigInteger('role_id')->default(3);
            $table->unsignedBigInteger('employed_by')->nullable();
            $table->string('status')->default(-1);
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('employed_by')->references('id')->on('users');
            $table->foreign('login_method_id')->references('id')->on('login_methods');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
