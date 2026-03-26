<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->date('po_date')->nullable()->after('po_number');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')->nullable()->after('event_id')->constrained('purchase_orders')->nullOnDelete();
        });

        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('sponsor_code')->nullable()->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('event_series_sponsors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_series_id')->constrained('event_series')->cascadeOnDelete();
            $table->foreignId('sponsor_id')->constrained('sponsors')->cascadeOnDelete();
            $table->decimal('contribution_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['event_series_id', 'sponsor_id']);
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->foreignId('sponsor_id')->nullable()->after('account_id')->constrained('sponsors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sponsor_id');
        });

        Schema::dropIfExists('event_series_sponsors');
        Schema::dropIfExists('sponsors');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_order_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('po_date');
        });
    }
};
