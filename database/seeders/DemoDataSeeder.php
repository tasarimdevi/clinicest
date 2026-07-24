<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\VerificationTier;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use Illuminate\Database\Seeder;

/**
 * Minimal realistic sample data so the homepage and directories render
 * meaningfully in local dev. Not representative of production content —
 * see docs/10-roadmap.md Phase 1 for real onboarding volume targets.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $gb = Country::create([
            'iso2' => 'GB', 'iso3' => 'GBR', 'name' => 'United Kingdom', 'slug' => 'uk',
            'currency' => 'GBP', 'dial_code' => '+44', 'is_target' => true, 'tier' => 'primary',
        ]);
        $de = Country::create([
            'iso2' => 'DE', 'iso3' => 'DEU', 'name' => 'Germany', 'slug' => 'germany',
            'currency' => 'EUR', 'dial_code' => '+49', 'is_target' => true, 'tier' => 'primary',
        ]);
        Country::create([
            'iso2' => 'IE', 'iso3' => 'IRL', 'name' => 'Ireland', 'slug' => 'ireland',
            'currency' => 'EUR', 'dial_code' => '+353', 'is_target' => true, 'tier' => 'primary',
        ]);
        $tr = Country::create([
            'iso2' => 'TR', 'iso3' => 'TUR', 'name' => 'Turkey', 'slug' => 'turkey',
            'currency' => 'TRY', 'dial_code' => '+90', 'is_target' => false, 'tier' => null,
        ]);

        $istanbul = City::create([
            'country_id' => $tr->id, 'name' => 'Istanbul', 'slug' => 'istanbul',
            'lat' => 41.0082, 'lng' => 28.9784, 'airport_code' => 'IST',
        ]);

        $cosmetic = TreatmentCategory::create(['name' => ['en' => 'Cosmetic'], 'slug' => 'cosmetic', 'sort' => 1]);
        $restorative = TreatmentCategory::create(['name' => ['en' => 'Restorative'], 'slug' => 'restorative', 'sort' => 2]);

        $treatments = [
            ['slug' => 'dental-implants', 'name' => 'Dental Implants', 'category' => $restorative, 'min' => 45000, 'max' => 90000],
            ['slug' => 'all-on-4', 'name' => 'All-on-4', 'category' => $restorative, 'min' => 250000, 'max' => 400000],
            ['slug' => 'hollywood-smile', 'name' => 'Hollywood Smile', 'category' => $cosmetic, 'min' => 180000, 'max' => 350000],
            ['slug' => 'veneers', 'name' => 'Veneers', 'category' => $cosmetic, 'min' => 15000, 'max' => 35000],
            ['slug' => 'teeth-whitening', 'name' => 'Teeth Whitening', 'category' => $cosmetic, 'min' => 8000, 'max' => 15000],
            ['slug' => 'invisalign', 'name' => 'Invisalign', 'category' => $restorative, 'min' => 200000, 'max' => 350000],
        ];

        foreach ($treatments as $i => $t) {
            Treatment::create([
                'slug' => $t['slug'],
                'name' => ['en' => $t['name']],
                'summary' => ['en' => "{$t['name']} in Turkey — transparent pricing from verified clinics."],
                'category_id' => $t['category']->id,
                'base_price_min' => $t['min'],
                'base_price_max' => $t['max'],
                'currency' => 'EUR',
                'is_featured' => true,
                'sort' => $i,
                'status' => 'published',
            ]);
        }

        Clinic::create([
            'slug' => 'istanbul-smile-clinic',
            'name' => ['en' => 'Istanbul Smile Clinic'],
            'city_id' => $istanbul->id,
            'about' => ['en' => 'A verified, English-speaking dental clinic in central Istanbul.'],
            'phone' => '+90 212 000 0000',
            'whatsapp' => '+90 555 000 0000',
            'email' => 'info@example.com',
            'verification_tier' => VerificationTier::Elite,
            'verified_at' => now(),
            'response_time_minutes' => 90,
            'languages_json' => ['en', 'de', 'tr'],
            'rating_avg' => 4.9,
            'rating_count' => 128,
            'is_active' => true,
            'is_featured' => true,
        ]);
    }
}
