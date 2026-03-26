<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_series_id')->constrained('event_series')->cascadeOnDelete();
            $table->string('attendable_type', 50); // event_registrations or event_staff
            $table->unsignedBigInteger('attendable_id');
            $table->date('date');
            $table->dateTime('checked_in_at');
            $table->string('scanned_by_type', 30)->nullable();
            $table->unsignedBigInteger('scanned_by_id')->nullable();
            $table->timestamps();

            $table->unique(['attendable_type', 'attendable_id', 'date']);
            $table->index(['event_series_id', 'date']);
            $table->index(['attendable_type', 'attendable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
