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
        Schema::create('event_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id');
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('role_in_event');
            $table->boolean('attendance')->default(false);
            $table->decimal('cost_amount', 14, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'employee_id']);
            $table->index(['event_id', 'role_in_event']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_staff');
    }
};
