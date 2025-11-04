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
        Schema::create('notes', function (Blueprint $table) {
            $table->id('note_id');
            $table->foreignId('patient_id')
                ->constrained('patients', 'patient_id')
                ->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('urgent')->default(false);
            $table->foreignId('author_id')->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
