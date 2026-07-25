<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Laravel's standard notifications table (docs/05-database-schema-erd.md
     * §"Consent, audit, notifications, settings" — id/type/notifiable/
     * data_json/read_at). `User` already had the `Notifiable` trait wired
     * (unused until now) — this is what makes it functional. Only `User`
     * is notified in this pass (admin/clinic staff, who have an account
     * and a dashboard to see a bell icon in); `Lead`/patient notifications
     * stay plain transactional Mail, since a magic-link visitor has no
     * session to read an in-app notification list from.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
