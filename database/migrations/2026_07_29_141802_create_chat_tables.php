<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI Chat Assistant (docs/07-ai-architecture.md §2.4). `chat_sessions` holds
 * one row per widget conversation; `chat_messages` the turns within it;
 * `chat_settings` is a deliberate single-row table (not a generic key/value
 * store — none exists in this codebase yet) so the kill switch and abuse
 * caps can be tuned from the admin panel without a redeploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('open'); // open|converted|abandoned
            $table->string('locale', 12)->default('en');
            $table->json('page_context')->nullable(); // page/UTM the widget was opened from
            $table->string('ip_hash', 64); // sha256, never the raw IP
            $table->unsignedSmallInteger('message_count')->default(0);
            $table->unsignedInteger('token_count')->default(0);
            $table->boolean('consent')->default(false);
            $table->timestamps();

            $table->index(['ip_hash', 'created_at']);
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_session_id')->constrained()->cascadeOnDelete();
            $table->string('role'); // user|assistant|system|tool
            $table->text('content'); // guardrail-passed, user-visible text
            // Raw model draft, kept only for audit — never replayed to the
            // model as history (see ChatResponseVerifier docblock).
            $table->text('original_draft')->nullable();
            $table->string('tool_name')->nullable();
            $table->json('tool_input')->nullable();
            $table->json('tool_output')->nullable();
            $table->string('model')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->boolean('flagged')->default(false);
            $table->string('flag_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['chat_session_id', 'created_at']);
        });

        Schema::create('chat_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false); // kill switch — default OFF
            $table->unsignedInteger('daily_budget_tokens')->default(200000);
            $table->unsignedInteger('tokens_used_today')->default(0);
            $table->date('budget_date')->nullable();
            $table->unsignedSmallInteger('max_messages_per_session')->default(20);
            $table->unsignedSmallInteger('max_sessions_per_ip_per_hour')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_sessions');
        Schema::dropIfExists('chat_settings');
    }
};
