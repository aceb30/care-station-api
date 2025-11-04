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
        Schema::create('group_members', function (Blueprint $table) {
            // Foreign Key: user_id -> users(user_id) ON DELETE CASCADE
            $table->foreignId('user_id')
                ->constrained('users', 'user_id')
                ->cascadeOnDelete();

            // Foreign Key: care_group_id -> care_groups(care_group_id) ON DELETE CASCADE
            $table->foreignId('care_group_id')
                ->constrained('care_groups', 'care_group_id')
                ->cascadeOnDelete();
            
            // Composite Primary Key
            $table->primary(['user_id', 'care_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};
