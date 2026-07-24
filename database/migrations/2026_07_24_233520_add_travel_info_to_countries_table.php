<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            // Powers the /countries/{country} landing page's travel-info
            // section (docs/04-wireframes.md §7) and the clinic-language
            // match on "featured clinics serving this country".
            $table->string('primary_language', 12)->nullable()->after('dial_code');
            $table->string('flight_note')->nullable()->after('primary_language');
            $table->decimal('avg_flight_hours', 3, 1)->nullable()->after('flight_note');
            $table->string('visa_info')->nullable()->after('avg_flight_hours');
            $table->string('best_time_to_visit')->nullable()->after('visa_info');
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn([
                'primary_language', 'flight_note', 'avg_flight_hours', 'visa_info', 'best_time_to_visit',
            ]);
        });
    }
};
