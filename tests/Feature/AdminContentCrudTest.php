<?php

declare(strict_types=1);

use App\Livewire\Admin\Categories;
use App\Livewire\Admin\FaqForm;
use App\Livewire\Admin\Faqs;
use App\Livewire\Admin\TreatmentForm;
use App\Livewire\Admin\Treatments;
use App\Models\Faq;
use App\Models\PostCategory;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

// --- Treatments ---

it('lets an admin create a treatment in both locales, as a draft', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(TreatmentForm::class)
        ->set('name.en', 'Dental Implants')
        ->set('name.tr', 'Diş İmplantı')
        ->set('slug', 'dental-implants')
        ->set('priceMin', '450.00')
        ->set('priceMax', '900.00')
        ->set('currency', 'eur')
        ->set('sort', 3)
        ->call('save')
        ->assertHasNoErrors();

    $treatment = Treatment::where('slug', 'dental-implants')->firstOrFail();
    expect($treatment->getTranslation('name', 'en'))->toBe('Dental Implants');
    expect($treatment->getTranslation('name', 'tr'))->toBe('Diş İmplantı');
    expect($treatment->base_price_min)->toBe(45000);
    expect($treatment->base_price_max)->toBe(90000);
    expect($treatment->currency)->toBe('EUR');
    expect($treatment->status)->toBe('draft');
});

it('lets an admin publish and unpublish a treatment', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $treatment = Treatment::create(['slug' => 'veneers', 'name' => ['en' => 'Veneers'], 'status' => 'draft']);

    Livewire::actingAs($admin)
        ->test(TreatmentForm::class, ['treatment' => $treatment])
        ->call('publish')
        ->assertHasNoErrors();
    expect($treatment->fresh()->status)->toBe('published');

    Livewire::actingAs($admin)
        ->test(TreatmentForm::class, ['treatment' => $treatment->fresh()])
        ->call('unpublish');
    expect($treatment->fresh()->status)->toBe('draft');
});

it('blocks a content editor (no content.publish) from publishing a treatment', function () {
    $editor = User::factory()->create();
    $editor->assignRole('content_editor'); // content.view + content.edit, no publish
    $treatment = Treatment::create(['slug' => 'veneers', 'name' => ['en' => 'Veneers'], 'status' => 'draft']);

    Livewire::actingAs($editor)
        ->test(Treatments::class)
        ->call('togglePublish', $treatment->id)
        ->assertForbidden();

    expect($treatment->fresh()->status)->toBe('draft');
});

it('blocks a user without content.edit from creating a treatment', function () {
    $seo = User::factory()->create();
    $seo->assignRole('seo_manager'); // access-admin + content.view, no content.edit

    $this->actingAs($seo)->get(route('admin.treatments.create'))->assertForbidden();
});

it('lets a content editor create a draft treatment but not publish via status', function () {
    $editor = User::factory()->create();
    $editor->assignRole('content_editor');

    Livewire::actingAs($editor)
        ->test(TreatmentForm::class)
        ->set('name.en', 'Cleaning')
        ->set('slug', 'cleaning')
        ->set('sort', 0)
        ->call('save')
        ->assertHasNoErrors();

    expect(Treatment::where('slug', 'cleaning')->firstOrFail()->status)->toBe('draft');
});

// --- FAQs ---

it('lets an admin create a global FAQ', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(FaqForm::class)
        ->set('question.en', 'Is it safe?')
        ->set('answer.en', 'Yes, all clinics are verified.')
        ->set('status', 'published')
        ->set('sort', 0)
        ->call('save')
        ->assertHasNoErrors();

    $faq = Faq::first();
    expect($faq->faqable_id)->toBeNull();
    expect($faq->getTranslation('question', 'en'))->toBe('Is it safe?');
    expect($faq->status)->toBe('published');
});

it('lets an admin attach a FAQ to a treatment', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $treatment = Treatment::create(['slug' => 'implants', 'name' => ['en' => 'Implants'], 'status' => 'published']);

    Livewire::actingAs($admin)
        ->test(FaqForm::class)
        ->set('question.en', 'How long do implants last?')
        ->set('answer.en', 'Decades with care.')
        ->set('treatment_id', $treatment->id)
        ->set('status', 'published')
        ->set('sort', 0)
        ->call('save')
        ->assertHasNoErrors();

    $faq = Faq::first();
    expect($faq->faqable_type)->toBe(Treatment::class);
    expect($faq->faqable_id)->toBe($treatment->id);
});

it('blocks a content editor from publishing a new FAQ via the status field', function () {
    $editor = User::factory()->create();
    $editor->assignRole('content_editor');

    Livewire::actingAs($editor)
        ->test(FaqForm::class)
        ->set('question.en', 'Q?')
        ->set('answer.en', 'A.')
        ->set('status', 'published')
        ->set('sort', 0)
        ->call('save')
        ->assertForbidden();

    expect(Faq::count())->toBe(0);
});

it('lets a content editor create a draft FAQ', function () {
    $editor = User::factory()->create();
    $editor->assignRole('content_editor');

    Livewire::actingAs($editor)
        ->test(FaqForm::class)
        ->set('question.en', 'Q?')
        ->set('answer.en', 'A.')
        ->set('status', 'draft')
        ->set('sort', 0)
        ->call('save')
        ->assertHasNoErrors();

    expect(Faq::first()->status)->toBe('draft');
});

// --- Categories ---

it('lets an admin add a treatment category with a bilingual name', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(Categories::class)
        ->set('newTreatment.en', 'Cosmetic')
        ->set('newTreatment.tr', 'Estetik')
        ->set('newTreatment.slug', 'cosmetic')
        ->call('addTreatmentCategory')
        ->assertHasNoErrors();

    $category = TreatmentCategory::where('slug', 'cosmetic')->firstOrFail();
    expect($category->getTranslation('name', 'en'))->toBe('Cosmetic');
    expect($category->getTranslation('name', 'tr'))->toBe('Estetik');
});

it('lets an admin rename and delete a post category', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $category = PostCategory::create(['name' => ['en' => 'News'], 'slug' => 'news', 'sort' => 1]);

    $component = Livewire::actingAs($admin)->test(Categories::class);
    $component->set("postNames.{$category->id}.en", 'Announcements')->call('savePostCategory', $category->id);
    expect($category->fresh()->getTranslation('name', 'en'))->toBe('Announcements');

    $component->call('deletePostCategory', $category->id);
    expect(PostCategory::find($category->id))->toBeNull();
});

it('blocks a seo_manager (content.view only) from adding a category', function () {
    $seo = User::factory()->create();
    $seo->assignRole('seo_manager');

    Livewire::actingAs($seo)
        ->test(Categories::class)
        ->set('newTreatment.en', 'Sneaky')
        ->set('newTreatment.slug', 'sneaky')
        ->call('addTreatmentCategory')
        ->assertForbidden();

    expect(TreatmentCategory::where('slug', 'sneaky')->exists())->toBeFalse();
});
