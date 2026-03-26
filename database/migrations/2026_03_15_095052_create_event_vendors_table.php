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
        Schema::create('event_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id');
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('service_scope')->nullable();
            $table->decimal('cost_amount', 14, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['event_id', 'vendor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_vendors');
    }
};
