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
        Schema::create('medical_contacts', function (Blueprint $table) {
            $table->id('medical_contact_id');
            $table->foreignId('patient_id')
                ->constrained('patients', 'patient_id')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('cellphone', 20)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('mail')->nullable();
            $table->string('address')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_contacts');
    }
};
