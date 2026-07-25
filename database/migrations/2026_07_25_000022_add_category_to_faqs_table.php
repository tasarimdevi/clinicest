<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Only used by global FAQs (faqable_id null) to power the categorized
     * accordions on the site-wide /faq page (docs/04-wireframes.md §13).
     * Treatment/country-scoped FAQs leave this null.
     */
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->string('category')->nullable()->after('answer');
        });
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
