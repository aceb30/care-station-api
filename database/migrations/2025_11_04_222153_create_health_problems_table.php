<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('health_problems', function (Blueprint $table) {
            $table->id('health_problem_id');
            $table->foreignId('patient_id')
                ->constrained('patients', 'patient_id')
                ->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            // 0=allergy, 1=disability, 2=chronic illness, 3=other
            $table->tinyInteger('type')->nullable();
        });

        // Add the raw CHECK constraint
        DB::statement('ALTER TABLE health_problems ADD CONSTRAINT chk_health_problem_type CHECK (type IN (0, 1, 2, 3))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_problems');
    }
};
