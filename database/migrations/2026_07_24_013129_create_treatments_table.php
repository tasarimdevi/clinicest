<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name'); // translatable
            $table->json('summary')->nullable(); // translatable
            $table->json('body')->nullable(); // translatable rich content
            $table->foreignId('category_id')->nullable()->constrained('treatment_categories')->nullOnDelete();
            $table->string('icon')->nullable();
            $table->unsignedSmallInteger('avg_duration_min')->nullable();
            $table->unsignedSmallInteger('recovery_days')->nullable();
            $table->unsignedTinyInteger('trips_required')->nullable();
            $table->unsignedInteger('base_price_min')->nullable(); // minor units
            $table->unsignedInteger('base_price_max')->nullable(); // minor units
            $table->string('currency', 3)->default('EUR');
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
