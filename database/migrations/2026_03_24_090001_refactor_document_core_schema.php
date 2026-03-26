<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('document_attachments');
        Schema::dropIfExists('document_audits');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('document_categories');

        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('document_categories')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['text_based', 'table_based', 'mixed']);
            $table->json('form_schema')->nullable();
            $table->longText('template_layout')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('document_templates')->cascadeOnDelete();
            $table->string('title');
            $table->json('payload')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('document_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 50)->nullable();
            $table->string('disk', 50)->default('r2');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('document_categories')->insert([
            ['name' => 'Legal', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Operational', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Permits', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('document_attachments');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('document_categories');
    }
};
