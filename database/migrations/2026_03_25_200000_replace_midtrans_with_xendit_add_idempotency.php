<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            // 1. Check for event_series_id
            if (!Schema::hasColumn('event_registrations', 'event_series_id')) {
                $table->foreignId('event_series_id')->nullable()->after('event_id')
                    ->constrained('event_series')->cascadeOnDelete();
            }

            // 2. Check for idempotency_key
            if (!Schema::hasColumn('event_registrations', 'idempotency_key')) {
                $table->uuid('idempotency_key')->nullable()->unique()->after('registration_number');
            }

            // 3. Check for Xendit columns
            if (!Schema::hasColumn('event_registrations', 'xendit_invoice_id')) {
                $table->string('xendit_invoice_id')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('event_registrations', 'xendit_invoice_url')) {
                $table->string('xendit_invoice_url')->nullable()->after('xendit_invoice_id');
            }
            if (!Schema::hasColumn('event_registrations', 'xendit_external_id')) {
                $table->string('xendit_external_id')->nullable()->unique()->after('xendit_invoice_url');
            }

            // 4. Safely drop Midtrans columns
            if (Schema::hasColumn('event_registrations', 'midtrans_order_id')) {
                // Drop unique first to be clean
                $table->dropUnique(['midtrans_order_id']);
                $table->dropColumn(['midtrans_snap_token', 'midtrans_order_id', 'midtrans_transaction_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            // 1. Restore Midtrans columns (as they were before)
            $table->string('midtrans_snap_token')->nullable()->after('payment_status');
            $table->string('midtrans_order_id')->nullable()->unique()->after('midtrans_snap_token');
            $table->string('midtrans_transaction_id')->nullable()->after('midtrans_order_id');

            // 2. Drop Xendit and Idempotency columns
            // We drop the unique index first to be safe, then the columns
            $table->dropUnique(['xendit_external_id']);
            $table->dropUnique(['idempotency_key']);

            $table->dropColumn([
                'idempotency_key',
                'xendit_invoice_id',
                'xendit_invoice_url',
                'xendit_external_id'
            ]);

            // 3. Handle event_series_id
            // If this migration was the one that created it, we remove it.
            if (Schema::hasColumn('event_registrations', 'event_series_id')) {
                $table->dropForeign(['event_series_id']);
                $table->dropColumn('event_series_id');
            }
        });
    }
};
