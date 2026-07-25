<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Matches docs/05-database-schema-erd.md's `payments` spec. A payment
     * against an invoice. In this deterministic pass every payment is
     * provider='manual' (staff recording that a bank transfer / offline
     * payment cleared), status='succeeded' — there's no gateway callback
     * writing succeeded/failed/refunded rows yet. `raw_json` is the seam
     * for a future gateway's webhook payload.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('manual'); // manual|stripe|iyzico
            $table->string('provider_ref')->nullable();
            $table->unsignedInteger('amount'); // minor units
            $table->string('currency', 3)->default('EUR');
            $table->string('status')->default('succeeded'); // App\Enums\PaymentStatus
            $table->string('method')->nullable(); // bank_transfer|card|...
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
