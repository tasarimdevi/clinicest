<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('public_id')->unique()->nullable()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->string('whatsapp')->nullable()->after('phone');
            $table->foreignId('country_id')->nullable()->after('whatsapp')->constrained('countries')->nullOnDelete();
            $table->string('locale', 12)->default('en')->after('country_id');
            $table->string('avatar_path')->nullable()->after('locale');
            $table->enum('status', ['active', 'suspended'])->default('active')->after('avatar_path');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
            $table->dropColumn([
                'public_id', 'phone', 'whatsapp', 'locale',
                'avatar_path', 'status', 'last_login_at', 'deleted_at',
            ]);
        });
    }
};
