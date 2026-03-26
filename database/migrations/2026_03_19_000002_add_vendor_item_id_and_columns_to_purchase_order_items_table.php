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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('vendor_item_id')->nullable()->after('purchase_order_id')->constrained('vendor_items')->nullOnDelete();
            $table->string('description')->nullable()->after('item_name');
            $table->string('unit')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_item_id');
            $table->dropColumn(['description', 'unit']);
        });
    }
};
