<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('iso2', 2)->unique();
            $table->string('iso3', 3)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('currency', 3); // ISO-4217
            $table->string('dial_code', 8)->nullable();
            $table->string('flag_path')->nullable();
            $table->boolean('is_target')->default(false);
            $table->enum('tier', ['primary', 'secondary', 'future'])->nullable();
            $table->timestamps();

            $table->index(['is_target', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
