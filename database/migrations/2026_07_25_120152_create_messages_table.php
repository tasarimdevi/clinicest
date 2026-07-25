<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * docs/09-crm-admin-architecture.md §3: "Patient messages: threaded
     * chat (web + WhatsApp bridge), attachments, templates, translation
     * assist." Reduced scope:
     * - No WhatsApp/email bridge — those need a provider integration
     *   (Twilio, an inbound-email webhook) that doesn't exist in this
     *   project. `channel=web` is the one channel this system actually
     *   sends (as a real email to the lead, since there's no patient
     *   portal to receive an in-app message — see the SendLeadMessage
     *   docblock); `email`/`whatsapp`/`call` are staff manually logging
     *   a conversation that happened outside the platform, so nothing
     *   about a patient's communication history gets lost even before a
     *   real bridge exists.
     * - No attachments, no templates, no AI translation assist (no
     *   ANTHROPIC_API_KEY configured, same cut as the AI Cost Estimator).
     * One row per (lead, clinic) pair's conversation — same scoping as
     * offers/appointments, not a single global thread per lead, since a
     * lead can be talking to more than one clinic at once.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('sender'); // staff user who composed/logged it
            $table->string('direction'); // outbound|inbound
            $table->string('channel'); // web|email|whatsapp|call
            $table->text('body');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['lead_id', 'clinic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
