<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * docs/09-crm-admin-architecture.md §3: "Documents: upload/share
     * treatment plans, x-ray responses, invoices, certificates;
     * verification-doc uploads." Unlike the pasted-URL fields used
     * elsewhere in this project (Post.hero_image_path, Clinic.
     * credentials_url), this feature genuinely IS file upload, so this
     * pass builds real storage rather than another link field — via
     * Livewire's built-in WithFileUploads and the app's own 'local' disk
     * (storage/app/private — not the 'public' disk, since treatment
     * plans and x-rays are sensitive; every download goes through an
     * authorized route, never a guessable public URL). No S3/cloud
     * storage config needed for this to work correctly.
     * `lead_id` is nullable: null means a clinic-level document
     * (certificate, verification upload); set means a document tied to
     * one patient's case (treatment plan, x-ray), same optionality
     * docs implies by listing both kinds under one feature.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type'); // App\Enums\DocumentType
            $table->string('title');
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedInteger('file_size'); // bytes
            $table->string('mime_type');
            $table->timestamps();

            $table->index(['clinic_id', 'lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
