<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restores the `commissions.invoice_id` column that docs/05 always had
     * but the original commissions migration deliberately cut, because no
     * Invoice entity existed yet (see that migration's docblock). Now that
     * invoices exist, a commission that reaches status 'invoiced' points at
     * the Invoice generated for it (see LeadDetail::updateCommissionStatus
     * and App\Actions\Billing\GenerateInvoice). Nullable — a pending/waived
     * commission has no invoice.
     */
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('status')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
        });
    }
};
