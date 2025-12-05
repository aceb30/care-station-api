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
        Schema::create('group_invitations', function (Blueprint $table) {
            $table->id('invitation_id');
            
            // Relación con el grupo de cuidado
            $table->foreignId('care_group_id')
                ->constrained('care_groups', 'care_group_id')
                ->cascadeOnDelete();

            // El código en sí (ej. "ABC-123")
            $table->string('code', 10)->unique();
            
            // Fecha de expiración (para seguridad, que el código dure solo 24h o 48h)
            $table->timestamp('expires_at');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_invitations');
    }
};