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
        Schema::create('patients', function (Blueprint $table) {
            $table->id('patient_id');
            $table->foreignId('care_group_id')
                ->constrained('care_groups', 'care_group_id')
                ->cascadeOnDelete();
            $table->string('names', 100);
            $table->string('surnames', 100)->nullable();
            $table->string('cellphone', 20)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
