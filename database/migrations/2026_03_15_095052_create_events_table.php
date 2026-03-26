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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('event_number')->unique();
            $table->string('name');
            $table->string('venue')->nullable();
            $table->string('status')->default('planning')->index();
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable()->index();
            $table->json('timeline')->nullable();
            $table->decimal('budget_amount', 14, 2)->default(0);
            $table->decimal('revenue_amount', 14, 2)->default(0);
            $table->decimal('expense_amount', 14, 2)->default(0);
            $table->decimal('profit_amount', 14, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
