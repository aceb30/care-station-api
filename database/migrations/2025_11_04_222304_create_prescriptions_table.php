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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id('prescription_id');
            $table->foreignId('patient_id')
                ->constrained('patients', 'patient_id')
                ->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('emission_date')->nullable();
            $table->string('file_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
