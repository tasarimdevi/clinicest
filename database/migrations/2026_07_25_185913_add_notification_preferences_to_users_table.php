<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * docs/09-crm-admin-architecture.md §5's "user-configurable
     * preferences" — no column shape is specified there, so this is a
     * free-form map keyed by Notification FQCN, e.g.
     * {"App\\Notifications\\LeadAssignedToClinic": {"mail": false}}.
     * Missing keys default to "wants email" (see User::wantsEmailFor()) —
     * nullable/empty means no preferences have been set yet, not "opted
     * out of everything".
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('notification_preferences')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notification_preferences');
        });
    }
};
