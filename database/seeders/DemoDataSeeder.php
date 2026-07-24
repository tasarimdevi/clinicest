<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\VerificationTier;
use App\Models\BeforeAfterCase;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\CountryTreatment;
use App\Models\Doctor;
use App\Models\Faq;
use App\Models\Review;
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
            'primary_language' => 'en',
            'flight_note' => 'Direct flights from London, Manchester & Edinburgh to Istanbul.',
            'avg_flight_hours' => 4.0,
            'visa_info' => 'UK passport holders can enter Turkey visa-free for tourism stays up to 90 days.',
            'best_time_to_visit' => 'April–June and September–October, for mild weather and fewer crowds.',
        ]);
        $de = Country::create([
            'iso2' => 'DE', 'iso3' => 'DEU', 'name' => 'Germany', 'slug' => 'germany',
            'currency' => 'EUR', 'dial_code' => '+49', 'is_target' => true, 'tier' => 'primary',
            'primary_language' => 'de',
            'flight_note' => 'Direct flights from Frankfurt, Munich, Berlin & Cologne to Istanbul.',
            'avg_flight_hours' => 3.5,
            'visa_info' => 'German passport holders can enter Turkey visa-free for tourism stays up to 90 days.',
            'best_time_to_visit' => 'April–June and September–October, for mild weather and fewer crowds.',
        ]);
        $ie = Country::create([
            'iso2' => 'IE', 'iso3' => 'IRL', 'name' => 'Ireland', 'slug' => 'ireland',
            'currency' => 'EUR', 'dial_code' => '+353', 'is_target' => true, 'tier' => 'primary',
            'primary_language' => 'en',
            'flight_note' => 'Direct and one-stop flights from Dublin to Istanbul.',
            'avg_flight_hours' => 4.5,
            'visa_info' => 'Irish passport holders can enter Turkey visa-free for tourism stays up to 90 days.',
            'best_time_to_visit' => 'April–June and September–October, for mild weather and fewer crowds.',
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

        $treatmentDefs = [
            ['slug' => 'dental-implants', 'name' => 'Dental Implants', 'category' => $restorative, 'min' => 45000, 'max' => 90000],
            ['slug' => 'all-on-4', 'name' => 'All-on-4', 'category' => $restorative, 'min' => 250000, 'max' => 400000],
            ['slug' => 'hollywood-smile', 'name' => 'Hollywood Smile', 'category' => $cosmetic, 'min' => 180000, 'max' => 350000],
            ['slug' => 'veneers', 'name' => 'Veneers', 'category' => $cosmetic, 'min' => 15000, 'max' => 35000],
            ['slug' => 'teeth-whitening', 'name' => 'Teeth Whitening', 'category' => $cosmetic, 'min' => 8000, 'max' => 15000],
            ['slug' => 'invisalign', 'name' => 'Invisalign', 'category' => $restorative, 'min' => 200000, 'max' => 350000],
        ];

        $treatments = [];
        foreach ($treatmentDefs as $i => $t) {
            $treatments[$t['slug']] = Treatment::create([
                'slug' => $t['slug'],
                'name' => ['en' => $t['name']],
                'summary' => ['en' => "{$t['name']} in Turkey — transparent pricing from verified clinics."],
                'body' => ['en' => "Our verified clinics perform {$t['name']} using modern equipment and internationally trained dentists, at a fraction of UK/EU private pricing."],
                'category_id' => $t['category']->id,
                'avg_duration_min' => 90,
                'recovery_days' => 7,
                'trips_required' => 1,
                'base_price_min' => $t['min'],
                'base_price_max' => $t['max'],
                'currency' => 'EUR',
                'is_featured' => true,
                'sort' => $i,
                'status' => 'published',
            ]);
        }

        // Home-country vs. Turkey price comparison rows powering the
        // /countries/{country} and /cost/{treatment} landing pages.
        // Local prices are realistic ballpark private-dental figures for
        // each market; Turkey prices are the treatment's own EUR base
        // price converted to the country's currency (GBP uses a fixed
        // approximate rate since this is demo data, not live FX).
        $eurToGbp = 0.86;
        $localPriceDefs = [
            'dental-implants' => ['uk' => [200000, 300000], 'germany' => [150000, 250000], 'ireland' => [220000, 320000]],
            'all-on-4' => ['uk' => [1500000, 2500000], 'germany' => [1200000, 2000000], 'ireland' => [1600000, 2400000]],
            'hollywood-smile' => ['uk' => [800000, 1500000], 'germany' => [600000, 1200000], 'ireland' => [700000, 1300000]],
            'veneers' => ['uk' => [300000, 600000], 'germany' => [250000, 500000], 'ireland' => [300000, 550000]],
            'teeth-whitening' => ['uk' => [30000, 60000], 'germany' => [25000, 50000], 'ireland' => [30000, 55000]],
            'invisalign' => ['uk' => [300000, 550000], 'germany' => [250000, 450000], 'ireland' => [280000, 500000]],
        ];

        foreach ($localPriceDefs as $slug => $byCountry) {
            $t = $treatments[$slug];

            CountryTreatment::create([
                'country_id' => $gb->id,
                'treatment_id' => $t->id,
                'currency' => 'GBP',
                'local_price_min' => $byCountry['uk'][0],
                'local_price_max' => $byCountry['uk'][1],
                'turkey_price_min' => (int) round($t->base_price_min * $eurToGbp),
                'turkey_price_max' => (int) round($t->base_price_max * $eurToGbp),
            ]);
            CountryTreatment::create([
                'country_id' => $de->id,
                'treatment_id' => $t->id,
                'currency' => 'EUR',
                'local_price_min' => $byCountry['germany'][0],
                'local_price_max' => $byCountry['germany'][1],
                'turkey_price_min' => $t->base_price_min,
                'turkey_price_max' => $t->base_price_max,
            ]);
            CountryTreatment::create([
                'country_id' => $ie->id,
                'treatment_id' => $t->id,
                'currency' => 'EUR',
                'local_price_min' => $byCountry['ireland'][0],
                'local_price_max' => $byCountry['ireland'][1],
                'turkey_price_min' => $t->base_price_min,
                'turkey_price_max' => $t->base_price_max,
            ]);
        }

        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['dental-implants']->id,
            'question' => ['en' => 'Is it safe to get dental implants abroad?'],
            'answer' => ['en' => 'Yes, provided the clinic is properly verified. Every Clinicest partner clinic passes a documented licensing and sterilization check before being listed.'],
            'sort' => 1,
        ]);
        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['dental-implants']->id,
            'question' => ['en' => 'How many trips to Turkey do I need?'],
            'answer' => ['en' => 'Most implant cases are completed in a single trip of 5-7 days, though complex cases may need a short follow-up visit.'],
            'sort' => 2,
        ]);

        $clinic = Clinic::create([
            'slug' => 'istanbul-smile-clinic',
            'name' => ['en' => 'Istanbul Smile Clinic'],
            'city_id' => $istanbul->id,
            'about' => ['en' => 'A verified, English-speaking dental clinic in central Istanbul.'],
            'phone' => '+90 212 000 0000',
            'whatsapp' => '+90 555 000 0000',
            'email' => 'info@example.com',
            'website' => 'https://istanbulsmileclinic.example.com',
            'founded_year' => 2011,
            'patients_treated' => 1900,
            'verification_tier' => VerificationTier::Elite,
            'verified_at' => now(),
            'response_time_minutes' => 90,
            'languages_json' => ['en', 'de', 'tr'],
            'is_active' => true,
            'is_featured' => true,
        ]);

        foreach (['dental-implants', 'all-on-4', 'hollywood-smile', 'veneers'] as $slug) {
            $t = $treatments[$slug];
            $clinic->treatments()->attach($t->id, [
                'price_min' => $t->base_price_min,
                'price_max' => $t->base_price_max,
                'currency' => $t->currency,
                'is_available' => true,
            ]);
        }

        $doctor = Doctor::create([
            'slug' => 'dr-elif-kaya',
            'clinic_id' => $clinic->id,
            'full_name' => 'Dr. Elif Kaya',
            'title' => ['en' => 'DDS'],
            'specialty' => ['en' => 'Prosthodontics'],
            'bio' => ['en' => 'Dr. Kaya has 14 years of experience and has completed over 2,300 implant cases, with training in Istanbul and Berlin.'],
            'years_experience' => 14,
            'languages_json' => ['en', 'de', 'tr'],
            'is_featured' => true,
        ]);

        // Reviews — approved and seeded directly (no submission form or
        // moderation queue yet, see App\Models\Review docblock). Ratings
        // are honest, small numbers rather than an inflated placeholder
        // count, and clinic/doctor rating_avg/rating_count below are
        // computed FROM these rows so the two never drift apart.
        $clinicReviews = [
            ['name' => 'Sarah M.', 'country' => $gb, 'rating' => 5, 'verified' => true, 'treatment' => 'all-on-4',
                'title' => 'Saved thousands and the care was excellent', 'body' => 'I saved almost £9,000 on my All-on-4 and the clinic organised my airport transfer and hotel. I felt safer than I expected to.'],
            ['name' => 'Michael B.', 'country' => $de, 'rating' => 4, 'verified' => true, 'treatment' => 'dental-implants',
                'title' => 'Professional and transparent pricing', 'body' => 'Everything was explained clearly before I travelled. The only reason it is not five stars is the wait at check-in was longer than expected.'],
            ['name' => 'James T.', 'country' => $ie, 'rating' => 5, 'verified' => false, 'treatment' => null,
                'title' => 'Great experience overall', 'body' => 'Friendly staff, modern clinic, and the English-speaking coordinator made the whole trip easy to plan.'],
        ];

        foreach ($clinicReviews as $r) {
            Review::create([
                'reviewable_type' => Clinic::class,
                'reviewable_id' => $clinic->id,
                'reviewer_name' => $r['name'],
                'reviewer_country_id' => $r['country']->id,
                'rating' => $r['rating'],
                'title' => $r['title'],
                'body' => $r['body'],
                'treatment_id' => $r['treatment'] ? $treatments[$r['treatment']]->id : null,
                'is_verified' => $r['verified'],
                'status' => 'approved',
                'moderated_at' => now(),
            ]);
        }

        $clinic->update([
            'rating_avg' => round(collect($clinicReviews)->avg('rating'), 2),
            'rating_count' => count($clinicReviews),
        ]);

        Review::create([
            'reviewable_type' => Doctor::class,
            'reviewable_id' => $doctor->id,
            'reviewer_name' => 'Sarah M.',
            'reviewer_country_id' => $gb->id,
            'rating' => 5,
            'title' => 'Dr. Kaya was excellent',
            'body' => 'Very reassuring throughout the whole procedure and spoke perfect English.',
            'treatment_id' => $treatments['all-on-4']->id,
            'is_verified' => true,
            'status' => 'approved',
            'moderated_at' => now(),
        ]);

        $doctor->update(['rating_avg' => 5.0, 'rating_count' => 1]);

        // Before/after cases — photos are left null on purpose (see
        // migration comment): no real clinic photos exist yet, and this
        // platform never substitutes a placeholder image for a genuine
        // patient result. The page renders these honestly as "pending".
        BeforeAfterCase::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'treatment_id' => $treatments['all-on-4']->id,
            'title' => ['en' => 'Full-arch restoration, 1-week trip'],
            'description' => ['en' => 'Patient from the UK travelled for a single week to complete an All-on-4 full-arch restoration.'],
            'patient_country_id' => $gb->id,
            'consent_confirmed' => true,
            'is_published' => true,
        ]);
        BeforeAfterCase::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'treatment_id' => $treatments['hollywood-smile']->id,
            'title' => ['en' => 'Hollywood Smile veneer set'],
            'patient_country_id' => $de->id,
            'consent_confirmed' => true,
            'is_published' => true,
        ]);
    }
}
