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
        Schema::create('medication_prescriptions', function (Blueprint $table) {
            $table->foreignId('medication_id')
                ->constrained('medications', 'medication_id')
                ->cascadeOnDelete();
            $table->foreignId('prescription_id')
                ->constrained('prescriptions', 'prescription_id')
                ->cascadeOnDelete();
            
            $table->primary(['medication_id', 'prescription_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_prescriptions');
    }
};
