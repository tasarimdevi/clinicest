<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('slug')->unique();
            $table->json('name'); // translatable
            $table->string('legal_name')->nullable();
            $table->foreignId('city_id')->constrained()->restrictOnDelete();
            $table->string('address')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->json('about')->nullable(); // translatable
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->unsignedInteger('patients_treated')->nullable();
            $table->string('verification_tier')->default('pending'); // App\Enums\VerificationTier
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('response_time_minutes')->nullable();
            $table->json('languages_json')->nullable(); // ["en","de","tr"]
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['city_id', 'is_active', 'verification_tier']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
