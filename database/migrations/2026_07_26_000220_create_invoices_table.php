<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Matches docs/05-database-schema-erd.md's `invoices` spec. A single
     * invoice can bill either a subscription or a commission (polymorphic
     * `billable`), or stand alone (clinic-level, billable null). `number`
     * is a human-facing sequential reference; `public_id` (UUID) is what's
     * exposed externally, per the schema's enumeration-avoidance rule.
     *
     * Deterministic-only cuts: `pdf_path` stays null (no PDF generation
     * library wired) and `provider_ref` stays null (no payment gateway) —
     * both are the seam for the flagged real-billing work. `tax` is kept
     * (0 by default) so totals are already tax-aware when a real rate is
     * introduced. Voiding, not deleting, retires an invoice (financial
     * records aren't soft-deleted).
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('number')->unique();
            $table->nullableMorphs('billable'); // subscription|commission, or null for clinic-level
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount'); // minor units, pre-tax
            $table->unsignedInteger('tax')->default(0);
            $table->unsignedInteger('total'); // minor units, amount + tax
            $table->string('currency', 3)->default('EUR');
            $table->string('status')->default('draft'); // App\Enums\InvoiceStatus
            $table->timestamp('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('pdf_path')->nullable(); // null — no PDF generation yet
            $table->string('provider_ref')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
