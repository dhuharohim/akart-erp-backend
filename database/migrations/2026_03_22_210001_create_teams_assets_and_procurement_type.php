<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('team_code')->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('lead_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('department_id')->constrained('teams')->nullOnDelete();
        });

        Schema::create('event_team_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_series_id')->constrained('event_series')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('role_in_event')->nullable();
            $table->decimal('cost_amount', 14, 2)->default(0);
            $table->boolean('attendance')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['event_series_id', 'team_id']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('procurement_type')->default('event')->after('vendor_id')->index();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->unique()->constrained('purchase_order_items')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('acquisition_cost', 14, 2)->default(0);
            $table->decimal('depreciation_rate', 5, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('procurement_type');
        });

        Schema::dropIfExists('event_team_assignments');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
        });

        Schema::dropIfExists('teams');
    }
};
