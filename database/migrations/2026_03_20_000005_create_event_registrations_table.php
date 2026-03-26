<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_price_series_id')->constrained('event_price_series')->cascadeOnDelete();
            $table->string('registration_number', 100)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('telephone', 50)->nullable();
            $table->unsignedInteger('age')->nullable();
            $table->text('address')->nullable();
            $table->json('custom_fields')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('payment_status', 30)->default('pending');
            $table->string('midtrans_snap_token')->nullable();
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('midtrans_transaction_id')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['event_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
