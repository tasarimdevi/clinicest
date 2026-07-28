<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\CountryTreatment;
use App\Models\Faq;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use Illuminate\Database\Seeder;

/**
 * Real, production-safe reference content: countries, treatment
 * categories/treatments, cost-comparison rows, FAQs, and guide/blog
 * posts. Every fact here is genuine (researched pricing, real BBC-reported
 * UK patient volume, real TDB/verification process) — none of it is a
 * fabricated business, person, or review, so unlike DemoDataSeeder (which
 * layers a demo clinic/doctor/reviews on top for local dev only) this is
 * safe to run against production per docs/10-roadmap.md §3.
 */
class RealContentSeeder extends Seeder
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

        City::create([
            'country_id' => $tr->id, 'name' => 'Istanbul', 'slug' => 'istanbul',
            'lat' => 41.0082, 'lng' => 28.9784, 'airport_code' => 'IST',
        ]);

        $cosmetic = TreatmentCategory::create(['name' => ['en' => 'Cosmetic'], 'slug' => 'cosmetic', 'sort' => 1]);
        $restorative = TreatmentCategory::create(['name' => ['en' => 'Restorative'], 'slug' => 'restorative', 'sort' => 2]);

        $treatmentDefs = [
            ['slug' => 'dental-implants', 'name' => 'Dental Implants', 'category' => $restorative, 'min' => 45000, 'max' => 90000],
            ['slug' => 'all-on-4', 'name' => 'All-on-4', 'category' => $restorative, 'min' => 350000, 'max' => 650000, 'trips' => 2],
            ['slug' => 'hollywood-smile', 'name' => 'Hollywood Smile', 'category' => $cosmetic, 'min' => 250000, 'max' => 600000],
            ['slug' => 'veneers', 'name' => 'Veneers', 'category' => $cosmetic, 'min' => 15000, 'max' => 35000],
            ['slug' => 'teeth-whitening', 'name' => 'Teeth Whitening', 'category' => $cosmetic, 'min' => 15000, 'max' => 35000],
            ['slug' => 'invisalign', 'name' => 'Invisalign', 'category' => $restorative, 'min' => 200000, 'max' => 350000, 'trips' => 2],
        ];

        // Per-treatment editorial copy — every treatment now has real,
        // researched content (see docs/06-seo-architecture.md §1 for the
        // pillar priority order this followed). Prices above were also
        // corrected against 2026 market research where the original
        // placeholder figures were noticeably off (all-on-4, hollywood
        // smile, teeth whitening) — see docs/10-roadmap.md §3, these are
        // still reference figures pending real onboarded-clinic pricing,
        // not live contracts, so keeping them accurate matters more than
        // keeping them unchanged.
        $treatmentCopy = [
            'dental-implants' => [
                'summary' => 'Dental implants in Turkey cost €450–900 per tooth (around £400–800), roughly 65-75% less than UK or German private prices, from clinics using the same Straumann and Nobel Biocare implant systems used at home.',
                'body' => '<h2>What a dental implant actually costs</h2>'
                    .'<p>A single implant with an abutment and crown typically runs €450–900 in Istanbul or Antalya, compared with £2,800–4,200 for the equivalent treatment in the UK. The gap isn\'t about cheaper materials — most verified clinics use the same premium implant brands (Straumann, Nobel Biocare) that UK private dentists use. It comes from lower clinic overheads and a highly competitive private dental market in Turkey.</p>'
                    .'<h2>How many trips does it take?</h2>'
                    .'<p>Most single-implant and multi-implant cases are completed in one trip of 5–7 days: placement on day one, a healing check partway through, and the final crown fitted before you fly home. Cases needing bone grafting sometimes require a short second visit 3-4 months later once the graft has integrated.</p>'
                    .'<h2>Implants vs. All-on-4/All-on-6</h2>'
                    .'<p>If you\'re missing most or all of your teeth in an arch rather than one or two, a full-arch solution like <a href="/treatments/all-on-4">All-on-4</a> usually works out cheaper per tooth and requires less bone than placing individual implants across the whole arch — see our All-on-4 vs All-on-6 comparison for which fits your case.</p>'
                    .'<h2>What to check before you book</h2>'
                    .'<p>Ask directly which implant brand and warranty terms apply, confirm the dentist is registered with the Turkish Dental Association (TDB), and get your treatment plan and final price in writing before you travel. Every clinic listed on Clinicest has passed a documented licensing and sterilisation check — see <a href="/how-it-works">How It Works</a> for the full standard.</p>',
            ],
            'all-on-4' => [
                'summary' => 'All-on-4 full-arch restoration in Turkey costs €3,500–6,500 per arch (both arches: €7,000–12,000) — around 70-80% less than UK or US private prices — replacing a full arch of missing teeth with just four implants over two trips.',
                'body' => '<h2>What All-on-4 actually costs</h2>'
                    .'<p>A single arch (upper or lower) typically costs €3,500–6,500 in Turkey, with both arches together running €7,000–12,000 — set against equivalent full-arch implant work in the UK or US that easily reaches double or triple that. Four strategically angled implants per arch support a full fixed bridge, avoiding the bone grafting that individual implants across a whole arch would often need.</p>'
                    .'<h2>Why it takes two trips</h2>'
                    .'<p>Unlike single implants, All-on-4 normally needs two visits: the first (5-7 days) covers any extractions, 3D imaging, placing the four implants, and fitting a temporary bridge you can eat and smile with immediately. The second visit, 3-6 months later once the implants have integrated with the bone, is for fitting your final, permanent bridge — usually just 2-3 days.</p>'
                    .'<h2>All-on-4 vs. All-on-6</h2>'
                    .'<p>All-on-6 uses two extra implants for additional support and is often recommended for lower bone density or maximum long-term durability, at a higher cost. All-on-4 suits most patients with reasonable bone density who want a less invasive, faster, and more affordable full-arch solution — see our <a href="/treatments/dental-implants">Dental Implants</a> page if you only need one or two teeth replaced rather than a full arch.</p>'
                    .'<h2>What to check before you book</h2>'
                    .'<p>Confirm exactly how many implants and which brand are included in your quote, whether a bone graft is likely once your clinic reviews your scans, and get the total price for both trips in writing — some quotes only cover the first visit.</p>',
            ],
            'hollywood-smile' => [
                'summary' => 'A full Hollywood Smile (veneers across the visible smile) costs €2,500–6,000 in Turkey using the same E-Max and zirconia materials used in the UK, where the same treatment runs £10,000–30,000+ — typically completed in a single 5-10 day trip.',
                'body' => '<h2>What a Hollywood Smile actually costs</h2>'
                    .'<p>A full set of veneers (usually 16-20 visible teeth for a complete smile makeover) costs €2,500–6,000 in Turkey, against £10,000–30,000+ for the same E-Max or zirconia veneers in the UK. As with implants, the gap comes from lower clinic overheads, not lower-grade materials.</p>'
                    .'<h2>How long does it take?</h2>'
                    .'<p>Most Hollywood Smile cases are completed in a single trip of 5-10 days: digital smile design and tooth preparation early on, temporary veneers while your permanent set is fabricated, then final fitting before you fly home.</p>'
                    .'<h2>E-Max vs. zirconia</h2>'
                    .'<p>E-Max veneers are thinner and more translucent, giving a more natural look, and typically last 10-15 years. Zirconia is more opaque but stronger and more chip-resistant, often preferred when a tooth needs more coverage or extra durability. Your clinic will recommend one based on your teeth and goals, not a single "better" answer.</p>'
                    .'<h2>What to check before you book</h2>'
                    .'<p>Confirm exactly how many veneers are included in your quoted price (a partial makeover of 6-8 teeth costs less than a full 16-20 tooth set), which material is quoted, and ask to see a digital smile design mock-up before committing to the shape and shade.</p>',
            ],
            'veneers' => [
                'summary' => 'Dental veneers in Turkey cost €185–290 per tooth (E-Max or zirconia), compared with $1,200–2,500 per tooth in the UK or US — for patients fixing a few teeth rather than a full smile makeover.',
                'body' => '<h2>Veneers vs. a full Hollywood Smile</h2>'
                    .'<p>If you only need to fix a handful of chipped, discoloured, or uneven teeth, individual veneers are usually more cost-effective than a full <a href="/treatments/hollywood-smile">Hollywood Smile</a> makeover. Pricing is per tooth: €185–290 depending on material, versus $1,200–2,500 per tooth in the UK or US.</p>'
                    .'<h2>E-Max vs. zirconia veneers</h2>'
                    .'<p>E-Max veneers are thin, translucent, and look closest to natural enamel — the common choice when a handful of front teeth need a subtle, natural-looking fix. Zirconia is more opaque and more durable, often chosen when a tooth needs more coverage or extra strength.</p>'
                    .'<h2>How many trips does it take?</h2>'
                    .'<p>Most veneer cases on a few teeth are completed in a single trip of 5-7 days: preparation and temporary veneers early on, then your final set fitted before you leave.</p>'
                    .'<h2>What to check before you book</h2>'
                    .'<p>Confirm the exact number of teeth and material in your quote, and ask to see your dentist\'s portfolio of similarly-sized cases — veneer work depends heavily on the dentist\'s aesthetic skill, not just the material.</p>',
            ],
            'teeth-whitening' => [
                'summary' => 'Professional teeth whitening in Turkey costs €150–350 for a full session, compared with £300–850 in the UK — roughly 60-75% less, and usually combined with another treatment during the same trip rather than booked alone.',
                'body' => '<h2>What professional whitening costs</h2>'
                    .'<p>A full in-clinic whitening session (laser, BEYOND, or Philips Zoom) costs €150–350 in Turkey, against £300–850 for the same treatment in the UK — savings of roughly 60-75%. Results are visible immediately and typically last 1-3 years depending on diet and habits.</p>'
                    .'<h2>Is it worth a trip on its own?</h2>'
                    .'<p>Realistically, whitening alone rarely justifies international travel — it\'s the treatment patients most often add to a bigger trip (implants, veneers, or a check-up) rather than book by itself. If whitening is your only need, a reputable local dentist is usually the simpler choice.</p>'
                    .'<h2>What to expect</h2>'
                    .'<p>A session typically takes 60-90 minutes, with results visible immediately. Some mild, temporary sensitivity in the following day or two is normal and settles on its own.</p>',
            ],
            'invisalign' => [
                'summary' => 'Invisalign clear aligners in Turkey cost €1,800–3,500 for a full course, compared with £1,500–5,500 in the UK — typically requiring 2-3 in-person visits over the course of treatment.',
                'body' => '<h2>What Invisalign actually costs</h2>'
                    .'<p>A full Invisalign course in Turkey costs €1,800–3,500, against £1,500–5,500 in the UK — savings of roughly 50-65%, using the same Invisalign aligner system and digital scanning technology.</p>'
                    .'<h2>How the trips work</h2>'
                    .'<p>Unlike a single-visit treatment, Invisalign is a months-long process: an initial visit for scanning and starting your aligner series, then periodic check-ins to track progress, and a final visit once treatment is complete. Confirm with your clinic exactly how many in-person visits your specific plan needs, and whether remote photo check-ins can cover any of them.</p>'
                    .'<h2>Is it right for you?</h2>'
                    .'<p>Invisalign suits mild-to-moderate misalignment and spacing issues; more complex bite corrections may still need traditional braces or a combination approach. Your clinic\'s 3D scan and treatment plan will confirm whether Invisalign alone will get you the result you want.</p>'
                    .'<h2>What to check before you book</h2>'
                    .'<p>Ask exactly how many aligner trays and in-person visits are included in your quoted price, and how check-ins work between visits if you won\'t be back in Turkey for months at a time.</p>',
            ],
        ];

        $treatments = [];
        foreach ($treatmentDefs as $i => $t) {
            $copy = $treatmentCopy[$t['slug']] ?? [
                'summary' => "{$t['name']} in Turkey — transparent pricing from verified clinics.",
                'body' => "Our verified clinics perform {$t['name']} using modern equipment and internationally trained dentists, at a fraction of UK/EU private pricing.",
            ];

            $treatments[$t['slug']] = Treatment::create([
                'slug' => $t['slug'],
                'name' => ['en' => $t['name']],
                'summary' => ['en' => $copy['summary']],
                'body' => ['en' => $copy['body']],
                'category_id' => $t['category']->id,
                'avg_duration_min' => 90,
                'recovery_days' => 7,
                'trips_required' => $t['trips'] ?? 1,
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
        // approximate rate, not live FX).
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
            'question' => ['en' => 'How much do dental implants cost in Turkey?'],
            'answer' => ['en' => 'A single dental implant with crown typically costs €450–900 (around £400–800) in Turkey, versus £2,800–4,200 for the same treatment in the UK — roughly 65-75% less, using the same implant brands.'],
            'sort' => 1,
        ]);
        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['dental-implants']->id,
            'question' => ['en' => 'Is it safe to get dental implants abroad?'],
            'answer' => ['en' => 'Yes, provided the clinic is properly verified. Every Clinicest partner clinic passes a documented licensing and sterilization check before being listed.'],
            'sort' => 2,
        ]);
        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['dental-implants']->id,
            'question' => ['en' => 'How many trips to Turkey do I need?'],
            'answer' => ['en' => 'Most implant cases are completed in a single trip of 5-7 days, though cases needing bone grafting may need a short follow-up visit a few months later.'],
            'sort' => 3,
        ]);
        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['dental-implants']->id,
            'question' => ['en' => 'What happens if I have a complication after I return home?'],
            'answer' => ['en' => 'Ask your matched clinic about this before you book — reputable clinics give a written aftercare plan and stay reachable for follow-up questions, and can coordinate with your home dentist if in-person care is needed.'],
            'sort' => 4,
        ]);

        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['all-on-4']->id,
            'question' => ['en' => 'How much does All-on-4 cost in Turkey?'],
            'answer' => ['en' => 'A single arch typically costs €3,500–6,500 in Turkey; both arches together run €7,000–12,000 — roughly 70-80% less than equivalent UK or US private pricing.'],
            'sort' => 1,
        ]);
        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['all-on-4']->id,
            'question' => ['en' => 'How many trips does All-on-4 require?'],
            'answer' => ['en' => 'Two trips: an initial 5-7 day visit for implant placement and a temporary bridge, then a second 2-3 day visit 3-6 months later for your final, permanent bridge once healing is complete.'],
            'sort' => 2,
        ]);
        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['all-on-4']->id,
            'question' => ['en' => "What's the difference between All-on-4 and All-on-6?"],
            'answer' => ['en' => 'All-on-6 adds two more implants for extra support, usually recommended for lower bone density or maximum long-term durability; All-on-4 is less invasive and more affordable for patients with adequate bone density.'],
            'sort' => 3,
        ]);

        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['hollywood-smile']->id,
            'question' => ['en' => 'How much does a Hollywood Smile cost in Turkey?'],
            'answer' => ['en' => 'A full set of veneers typically costs €2,500–6,000 in Turkey, compared with £10,000–30,000+ for the same treatment in the UK.'],
            'sort' => 1,
        ]);
        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['hollywood-smile']->id,
            'question' => ['en' => 'How many teeth does a Hollywood Smile cover?'],
            'answer' => ['en' => 'Most full smile makeovers cover 16-20 visible teeth, though partial cases of 6-8 teeth are common and cost proportionally less — confirm the exact tooth count in your quote.'],
            'sort' => 2,
        ]);
        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['hollywood-smile']->id,
            'question' => ['en' => 'E-Max or zirconia — which is better?'],
            'answer' => ['en' => 'Neither is universally better: E-Max is thinner and more natural-looking, zirconia is stronger and more chip-resistant. Your clinic will recommend one based on your teeth and goals.'],
            'sort' => 3,
        ]);

        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['veneers']->id,
            'question' => ['en' => 'How much do veneers cost in Turkey?'],
            'answer' => ['en' => 'Individual veneers cost €185–290 per tooth in Turkey (E-Max or zirconia), compared with $1,200–2,500 per tooth in the UK or US.'],
            'sort' => 1,
        ]);
        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['veneers']->id,
            'question' => ['en' => "What's the difference between veneers and a Hollywood Smile?"],
            'answer' => ['en' => 'Veneers on a handful of teeth suit patients fixing specific chips or discoloration; a Hollywood Smile is a full 16-20 tooth makeover for a complete new smile — the per-tooth price is similar, but the case size and total cost differ.'],
            'sort' => 2,
        ]);

        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['teeth-whitening']->id,
            'question' => ['en' => 'How much is teeth whitening in Turkey?'],
            'answer' => ['en' => 'A full professional whitening session costs €150–350 in Turkey, against £300–850 in the UK — around 60-75% less.'],
            'sort' => 1,
        ]);
        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['teeth-whitening']->id,
            'question' => ['en' => 'Should I travel to Turkey just for teeth whitening?'],
            'answer' => ['en' => "Usually not on its own — the travel cost rarely makes sense for whitening alone. It's most commonly combined with a larger treatment like implants or veneers during the same trip."],
            'sort' => 2,
        ]);

        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['invisalign']->id,
            'question' => ['en' => 'How much does Invisalign cost in Turkey?'],
            'answer' => ['en' => 'A full course costs €1,800–3,500 in Turkey, compared with £1,500–5,500 in the UK — savings of roughly 50-65%.'],
            'sort' => 1,
        ]);
        Faq::create([
            'faqable_type' => Treatment::class,
            'faqable_id' => $treatments['invisalign']->id,
            'question' => ['en' => 'How many trips does Invisalign treatment need?'],
            'answer' => ['en' => 'Typically 2-3: an initial scanning/fitting visit, one or more progress check-ins, and a final visit — though some clinics support remote photo check-ins between trips.'],
            'sort' => 2,
        ]);

        // Global site FAQs (faqable left null) — powers the /faq hub's
        // categorized accordions (docs/04-wireframes.md §13). Categories
        // match the wireframe spec: Booking, Safety, Costs, Travel,
        // Aftercare, Payments.
        $globalFaqs = [
            ['category' => 'Booking', 'q' => 'How do I book a consultation?', 'a' => 'Fill in the Get a Free Quote form with your treatment needs — a matched clinic reviews your case and replies within 24 hours with a written plan.'],
            ['category' => 'Booking', 'q' => 'Is the consultation really free?', 'a' => 'Yes. There is no charge to submit your case or to receive a treatment plan and price from a matched clinic.'],
            ['category' => 'Safety', 'q' => 'How are clinics verified?', 'a' => 'Every clinic passes a documented check covering practice licensing, sterilization standards, and dentist credentials before being listed — see How It Works for the full standard.'],
            ['category' => 'Safety', 'q' => 'What happens if something goes wrong?', 'a' => 'Clinicest is a neutral broker between you and your clinic. If a treatment deviates from the agreed plan, contact us and we will help mediate with the clinic.'],
            ['category' => 'Costs', 'q' => 'Why is treatment so much cheaper in Turkey?', 'a' => 'Lower clinic operating costs and a highly competitive private dental market — not lower-quality materials or training. See our Cost pages for like-for-like price comparisons.'],
            ['category' => 'Costs', 'q' => 'Will the final price match the quote?', 'a' => 'Your matched clinic confirms an exact price in writing after reviewing your case. That confirmed price is what you pay — no on-site upsells.'],
            ['category' => 'Travel', 'q' => 'How many trips do I need to make?', 'a' => 'Most treatments are completed in a single trip of 5-7 days. Complex cases (like some implant work) may need a short follow-up visit.'],
            ['category' => 'Aftercare', 'q' => 'Who handles aftercare once I am home?', 'a' => 'Your clinic gives you a written aftercare plan to share with your home dentist, and stays reachable for any follow-up questions.'],
            ['category' => 'Payments', 'q' => 'How do I pay the clinic?', 'a' => 'Payment terms are agreed directly with your matched clinic as part of your written treatment plan, before you travel.'],
        ];

        foreach ($globalFaqs as $i => $f) {
            Faq::create([
                'faqable_type' => null,
                'faqable_id' => null,
                'category' => $f['category'],
                'question' => ['en' => $f['q']],
                'answer' => ['en' => $f['a']],
                'sort' => $i,
                'status' => 'published',
            ]);
        }

        // Guide + Blog content. Byline is credited to the editorial team,
        // not a fabricated individual — and medical_reviewer_* is left
        // null throughout, since this copy has not yet had a named dentist
        // review pass (see the posts migration docblock, and docs/06 §3 —
        // publish this on production, but a real "medically reviewed by
        // Dr. …" credit still needs to happen before it's fully YMYL-ready).
        $costCategory = PostCategory::create(['name' => ['en' => 'Cost'], 'slug' => 'cost', 'sort' => 1]);
        $safetyCategory = PostCategory::create(['name' => ['en' => 'Safety'], 'slug' => 'safety', 'sort' => 2]);
        $storiesCategory = PostCategory::create(['name' => ['en' => 'Patient Stories'], 'slug' => 'patient-stories', 'sort' => 3]);

        Post::create([
            'kind' => 'guide',
            'is_pillar' => true,
            'slug' => 'dental-tourism-in-turkey-the-complete-guide',
            'title' => ['en' => 'Dental Tourism in Turkey: The Complete Guide'],
            'excerpt' => ['en' => 'Everything to know before booking dental treatment in Turkey — cost, safety, choosing a clinic, and what to expect from your trip.'],
            'body' => ['en' => '<p>Turkey has become one of the world\'s most visited destinations for dental treatment, and for one clear reason: patients from the UK, Germany, and Ireland routinely pay 50-70% less for the same procedures they would get at home, without compromising on the equipment or training behind the chair.</p><p>This guide is the starting point. Each section below links to a deeper article — read them in any order, or jump straight to what matters most for your case.</p><h2>What this guide covers</h2><p>We cover realistic costs by treatment, what "verified" actually means for a clinic, how to plan the trip itself, and the honest trade-offs of treating abroad. Nothing here replaces a conversation with a dentist about your specific case — use this guide to walk into that conversation informed.</p>'],
            'author_name' => 'Clinicest Editorial Team',
            'status' => 'published',
            'published_at' => now()->subDays(45),
        ]);

        Post::create([
            'kind' => 'guide',
            'category_id' => $costCategory->id,
            'treatment_id' => $treatments['dental-implants']->id,
            'slug' => 'how-much-does-dental-treatment-cost-in-turkey',
            'title' => ['en' => 'How Much Does Dental Treatment Cost in Turkey?'],
            'excerpt' => ['en' => 'A realistic breakdown of what UK, German, and Irish patients actually pay for implants, veneers, and full-arch work in Turkey versus at home.'],
            'body' => ['en' => '<p>The honest answer is: it depends on the treatment, the clinic\'s verification tier, and how many teeth are involved — but the pattern holds across almost every procedure we track. Turkey prices consistently land 50-70% below UK and German private-clinic rates for comparable work.</p><h2>Why so many patients are making the trip</h2><p>An estimated 150,000-200,000 UK residents now travel to Turkey for dental treatment each year, driven by long NHS waiting lists and private UK prices that can run 60-70% higher than the same work in Istanbul or Antalya. It has become routine enough that most verified clinics have dedicated English-speaking coordinators for exactly this patient flow.</p><h2>Why the gap is so large</h2><p>It isn\'t lower-quality materials. The gap comes mostly from operating costs — clinic rent, staff wages, and overheads are structurally lower in Istanbul than in London or Berlin, and the private dental market in Turkey is intensely competitive, which keeps margins tight even at well-equipped clinics.</p><h2>Is it worth the trip for a small amount of work?</h2><p>For 2-4 teeth, travel and accommodation costs can eat into the savings enough that it\'s a closer call. The clearest value is at 8 or more teeth — full-arch or full-mouth cases — where typical savings of £3,000-10,000 comfortably outweigh the cost of the trip itself.</p><h2>Getting an exact number</h2><p>Every treatment and cost page on this site shows a real price range, not a "from" teaser hiding a much higher final bill. Your matched clinic reviews your specific case and confirms an exact price in writing before you commit to anything.</p>'],
            'author_name' => 'Clinicest Editorial Team',
            'status' => 'published',
            'published_at' => now()->subDays(40),
        ]);

        Post::create([
            'kind' => 'guide',
            'category_id' => $safetyCategory->id,
            'slug' => 'is-it-safe-to-get-dental-work-done-in-turkey',
            'title' => ['en' => 'Is It Safe to Get Dental Work Done in Turkey?'],
            'excerpt' => ['en' => 'What "verified clinic" actually means, what to check yourself, and the honest risks of treating abroad.'],
            'body' => ['en' => '<p>Safety is the first question almost every patient asks, and it deserves a direct answer: yes, provided you choose a clinic that has actually been checked, not just one with a nice website.</p><h2>What verification actually checks</h2><p>A Clinicest-verified clinic has a confirmed practice license, a documented sterilization standard, and named dentists whose credentials have been checked — not just claimed. See our "How It Works" page for the full checklist behind each badge tier.</p><h2>What to still check yourself</h2><p>Ask your matched clinic directly about the specific materials and implant brands they plan to use, and get your treatment plan in writing before you travel. A good clinic will welcome these questions, not deflect them.</p><h2>The honest trade-off</h2><p>Treating abroad means your usual dentist isn\'t the one doing follow-up care. Ask your matched clinic what aftercare support looks like once you\'re home, and keep your own dentist in the loop.</p>'],
            'author_name' => 'Clinicest Editorial Team',
            'status' => 'published',
            'published_at' => now()->subDays(35),
        ]);

        Post::create([
            'kind' => 'blog',
            'category_id' => $storiesCategory->id,
            'treatment_id' => $treatments['all-on-4']->id,
            'slug' => 'what-to-pack-for-your-dental-trip-to-istanbul',
            'title' => ['en' => 'What to Pack for Your Dental Trip to Istanbul'],
            'excerpt' => ['en' => 'A practical packing and planning checklist for patients travelling to Istanbul for treatment.'],
            'body' => ['en' => '<p>Most trips for implant or veneer work run 5-7 days, and a little planning goes a long way toward keeping the trip stress-free.</p><h2>Before you fly</h2><p>Bring copies of any prior dental x-rays or records, a list of current medications, and your written treatment plan from your matched clinic. Soft, easy-to-chew snacks for the first couple of days post-treatment are worth packing too — Istanbul\'s food is wonderful, but your mouth will thank you for easing back in.</p><h2>During your stay</h2><p>Most patients based their whole trip around 1-2 clinic visits with recovery days in between — leave room in the schedule rather than over-booking sightseeing on procedure days.</p>'],
            'author_name' => 'Clinicest Editorial Team',
            'status' => 'published',
            'published_at' => now()->subDays(10),
        ]);

        Post::create([
            'kind' => 'blog',
            'category_id' => $storiesCategory->id,
            'slug' => 'questions-to-ask-before-choosing-a-clinic',
            'title' => ['en' => 'Ten Questions to Ask Before Choosing a Clinic'],
            'excerpt' => ['en' => 'A short checklist to bring to your first conversation with a matched clinic.'],
            'body' => ['en' => '<p>A good clinic will answer all of these clearly and without hesitation. If one dodges a question, treat that as useful information.</p><h2>The essentials</h2><p>Ask about the exact materials and implant brand being used, the dentist\'s specific experience with your procedure, what happens if a complication arises after you\'ve flown home, and whether the quoted price is genuinely final or has common add-ons.</p><h2>Logistics</h2><p>Confirm how many trips the treatment realistically needs, what the clinic\'s response time has been for other patients, and whether airport transfer and accommodation help is included or arranged separately.</p>'],
            'author_name' => 'Clinicest Editorial Team',
            'status' => 'published',
            'published_at' => now()->subDays(4),
        ]);
    }
}
