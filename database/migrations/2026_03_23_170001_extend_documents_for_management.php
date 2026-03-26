<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'template_key')) {
                $table->string('template_key', 120)->nullable()->after('file_type');
            }
            if (!Schema::hasColumn('documents', 'category')) {
                $table->string('category', 80)->nullable()->after('template_key');
            }
            if (!Schema::hasColumn('documents', 'title')) {
                $table->string('title')->nullable()->after('category');
            }
            if (!Schema::hasColumn('documents', 'status_scope')) {
                $table->string('status_scope', 40)->nullable()->after('title');
            }
            if (!Schema::hasColumn('documents', 'version_number')) {
                $table->unsignedInteger('version_number')->default(1)->after('status_scope');
            }
            if (!Schema::hasColumn('documents', 'parent_document_id')) {
                $table->foreignId('parent_document_id')->nullable()->after('version_number')
                    ->constrained('documents')->nullOnDelete();
            }
            if (!Schema::hasColumn('documents', 'metadata')) {
                $table->json('metadata')->nullable()->after('parent_document_id');
            }
            if (!Schema::hasColumn('documents', 'generated_at')) {
                $table->timestamp('generated_at')->nullable()->after('metadata');
            }
            if (!Schema::hasColumn('documents', 'last_downloaded_at')) {
                $table->timestamp('last_downloaded_at')->nullable()->after('generated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'last_downloaded_at')) {
                $table->dropColumn('last_downloaded_at');
            }
            if (Schema::hasColumn('documents', 'generated_at')) {
                $table->dropColumn('generated_at');
            }
            if (Schema::hasColumn('documents', 'metadata')) {
                $table->dropColumn('metadata');
            }
            if (Schema::hasColumn('documents', 'parent_document_id')) {
                $table->dropConstrainedForeignId('parent_document_id');
            }
            if (Schema::hasColumn('documents', 'version_number')) {
                $table->dropColumn('version_number');
            }
            if (Schema::hasColumn('documents', 'status_scope')) {
                $table->dropColumn('status_scope');
            }
            if (Schema::hasColumn('documents', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('documents', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('documents', 'template_key')) {
                $table->dropColumn('template_key');
            }
        });
    }
};
