<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Matches docs/05-database-schema-erd.md's `commissions` spec, minus
     * `invoice_id`: this pass doesn't build a separate Invoice entity —
     * docs describes invoices as "auto-generated PDFs..., payment links,
     * dunning," which needs a PDF library and a payment provider
     * (Stripe/iyzico), neither of which exist in this project (same cut
     * already made for clinic subscriptions). `status=invoiced` here is
     * simply a manually-set stage meaning "billed to the clinic outside
     * the system," not a generated document — still a real, useful
     * workflow stage without the infra a real Invoice model would need.
     * `notes` isn't in the docs spec either but covers "why waived/
     * disputed" without inventing a separate reason column per status,
     * same choice already made for treatment_cases' own `notes` field.
     */
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_case_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('base_amount'); // minor units, = treatment_case.agreed_price at generation time
            $table->decimal('rate_pct', 5, 2);
            $table->unsignedInteger('amount'); // minor units, base_amount * rate_pct / 100
            $table->string('currency', 3)->default('EUR');
            $table->string('status')->default('pending'); // pending|invoiced|paid|waived|disputed
            $table->date('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'status']);
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
