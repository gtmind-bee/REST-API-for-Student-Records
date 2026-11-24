<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('admission_number', 50);
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20);
            $table->string('department', 100);
            $table->string('level', 50);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['user_id', 'admission_number']);
            $table->unique(['user_id', 'email']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'department']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
