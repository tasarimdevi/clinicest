<?php

declare(strict_types=1);

use App\Livewire\Public\TreatmentsIndex;
use App\Models\BeforeAfterCase;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Faq;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use Livewire\Livewire;

it('renders the treatments hub with published treatments', function () {
    Treatment::create(['slug' => 'implants', 'name' => ['en' => 'Implants'], 'status' => 'published']);
    Treatment::create(['slug' => 'veneers', 'name' => ['en' => 'Veneers'], 'status' => 'published']);
    Treatment::create(['slug' => 'draft-one', 'name' => ['en' => 'Draft One'], 'status' => 'draft']);

    Livewire::test(TreatmentsIndex::class)
        ->assertSee('Implants')
        ->assertSee('Veneers')
        ->assertDontSee('Draft One');
});

it('filters the treatments hub by category', function () {
    $cosmetic = TreatmentCategory::create(['name' => ['en' => 'Cosmetic'], 'slug' => 'cosmetic']);
    $restorative = TreatmentCategory::create(['name' => ['en' => 'Restorative'], 'slug' => 'restorative']);

    Treatment::create(['slug' => 'veneers', 'name' => ['en' => 'Veneers'], 'status' => 'published', 'category_id' => $cosmetic->id]);
    Treatment::create(['slug' => 'implants', 'name' => ['en' => 'Implants'], 'status' => 'published', 'category_id' => $restorative->id]);

    Livewire::test(TreatmentsIndex::class)
        ->set('category', (string) $cosmetic->id)
        ->assertSee('Veneers')
        ->assertDontSee('Implants');
});

it('renders a published treatment detail page with faqs and route', function () {
    $treatment = Treatment::create([
        'slug' => 'dental-implants', 'name' => ['en' => 'Dental Implants'],
        'summary' => ['en' => 'A great treatment.'], 'base_price_min' => 45000, 'base_price_max' => 90000,
        'currency' => 'EUR', 'status' => 'published',
    ]);
    Faq::create([
        'faqable_type' => Treatment::class, 'faqable_id' => $treatment->id,
        'question' => ['en' => 'Is it safe?'], 'answer' => ['en' => 'Yes.'], 'sort' => 1,
    ]);

    $this->get(route('treatments.show', $treatment->slug))
        ->assertOk()
        ->assertSee('Dental Implants')
        ->assertSee('Is it safe?');
});

it('shows published before/after cases on the treatment detail page', function () {
    $treatment = Treatment::create(['slug' => 'implants', 'name' => ['en' => 'Implants'], 'status' => 'published']);

    $country = Country::create([
        'iso2' => 'TT', 'iso3' => 'TTT', 'name' => 'Testland', 'slug' => 'pt-testland',
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'pt-istanbul']);
    $clinic = Clinic::create(['slug' => 'pt-clinic', 'name' => ['en' => 'PT Clinic'], 'city_id' => $city->id, 'verification_tier' => 'verified', 'is_active' => true]);

    BeforeAfterCase::create([
        'clinic_id' => $clinic->id, 'treatment_id' => $treatment->id,
        'title' => ['en' => 'Treatment Page Case'], 'is_published' => true,
    ]);

    $this->get(route('treatments.show', $treatment->slug))
        ->assertOk()
        ->assertSee('Treatment Page Case');
});

it('404s for a draft treatment detail page', function () {
    $treatment = Treatment::create(['slug' => 'unpublished', 'name' => ['en' => 'Unpublished'], 'status' => 'draft']);

    $this->get(route('treatments.show', $treatment->slug))->assertNotFound();
});
