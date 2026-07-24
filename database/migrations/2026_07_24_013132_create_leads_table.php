<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('primary_treatment_id')->nullable()->constrained('treatments')->nullOnDelete();
            $table->json('treatments_json')->nullable(); // additional treatment ids
            $table->unsignedInteger('budget_min')->nullable(); // minor units
            $table->unsignedInteger('budget_max')->nullable(); // minor units
            $table->string('currency', 3)->default('EUR');
            $table->enum('timeline', ['asap', '1-3m', '3-6m', 'flexible'])->nullable();
            $table->text('message')->nullable();
            $table->json('source')->nullable(); // utm params
            $table->enum('channel', ['web', 'ai_advisor', 'whatsapp', 'referral'])->default('web');
            $table->string('status')->default('new'); // App\Enums\LeadStatus
            $table->unsignedTinyInteger('score')->nullable();
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('quality', ['cold', 'warm', 'hot'])->nullable();
            $table->string('locale', 12)->default('en');
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('lost_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['assigned_agent_id', 'status']);
            $table->index(['country_id', 'primary_treatment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
