<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('slug')->unique();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->json('title')->nullable(); // translatable
            $table->json('specialty')->nullable(); // translatable
            $table->json('bio')->nullable(); // translatable
            $table->unsignedTinyInteger('years_experience')->nullable();
            $table->json('languages_json')->nullable();
            $table->string('photo_path')->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
