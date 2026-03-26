<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'event_id')) {
                $table->dropConstrainedForeignId('event_id');
            }
            if (Schema::hasColumn('invoices', 'purchase_order_id')) {
                $table->dropConstrainedForeignId('purchase_order_id');
            }
            $table->string('related_type')->nullable()->after('company_id');
            $table->unsignedBigInteger('related_id')->nullable()->after('related_type');
            $table->index(['related_type', 'related_id'], 'invoices_related_index');
        });

        Schema::rename('invoice_items', 'invoice_details');
        Schema::table('invoice_details', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_details', 'item_name')) {
                $table->renameColumn('item_name', 'description');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('invoice_id')
                ->constrained('chart_of_accounts')->nullOnDelete();
        });

        Schema::create('venue_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->string('name');
            $table->enum('day_type', ['weekday', 'weekend', 'custom'])->default('custom');
            $table->unsignedInteger('period_days')->default(1);
            $table->decimal('amount', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('event_series_venue', function (Blueprint $table) {
            $table->foreignId('venue_price_id')->nullable()->after('venue_id')
                ->constrained('venue_prices')->nullOnDelete();
            $table->unsignedInteger('period_days')->nullable()->after('end_datetime');
            $table->decimal('amount', 14, 2)->nullable()->after('period_days');
        });
    }

    public function down(): void
    {
        Schema::table('event_series_venue', function (Blueprint $table) {
            $table->dropConstrainedForeignId('venue_price_id');
            $table->dropColumn(['period_days', 'amount']);
        });

        Schema::dropIfExists('venue_prices');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_id');
        });

        Schema::table('invoice_details', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_details', 'description')) {
                $table->renameColumn('description', 'item_name');
            }
        });
        Schema::rename('invoice_details', 'invoice_items');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_related_index');
            $table->dropColumn(['related_type', 'related_id']);
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
        });
    }
};
