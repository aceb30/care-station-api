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
        Schema::create('care_groups', function (Blueprint $table) {
            $table->id('care_group_id');
            $table->string('name', 100);
            $table->string('photo_url')->nullable();
            
            // Foreign Key: admin_id -> users(user_id) ON DELETE SET NULL
            $table->foreignId('admin_id')->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_groups');
    }
};
