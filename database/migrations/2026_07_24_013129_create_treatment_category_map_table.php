<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_category_map', function (Blueprint $table) {
            $table->foreignId('treatment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treatment_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['treatment_id', 'treatment_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_category_map');
    }
};
