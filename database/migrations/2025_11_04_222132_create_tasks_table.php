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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id('task_id');
            $table->foreignId('care_group_id')
                ->constrained('care_groups', 'care_group_id')
                ->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('frequency', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->timestampTz('begin_time')->nullable();
            $table->timestampTz('end_time')->nullable();
            $table->boolean('done')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
