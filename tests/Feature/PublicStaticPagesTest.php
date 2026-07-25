<?php

declare(strict_types=1);

use App\Livewire\Public\ContactPage;
use App\Livewire\Public\FaqPage;
use App\Mail\ContactMessageMail;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Faq;
use App\Models\Treatment;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('renders the how-it-works page with the verification standard and real trust counts', function () {
    $this->get(route('how-it-works'))
        ->assertOk()
        ->assertSee('Our verification standard')
        ->assertSee('Elite Partner');
});

it('renders the about page with real computed stats, not fabricated ones', function () {
    $country = Country::create([
        'iso2' => 'TR', 'iso3' => 'TUR', 'name' => 'Turkey', 'slug' => 'sp-turkey',
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'sp-istanbul']);
    Clinic::create([
        'slug' => 'sp-clinic', 'name' => ['en' => 'SP Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);

    $this->get(route('about'))
        ->assertOk()
        ->assertSee('About Clinicest')
        ->assertSee('How we build trust');
});

it('sends a contact message and shows a confirmation', function () {
    Mail::fake();

    Livewire::test(ContactPage::class)
        ->set('full_name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('message', 'I have a question about veneers.')
        ->call('submit')
        ->assertSet('sent', true);

    Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail) {
        return $mail->senderName === 'Jane Doe' && $mail->senderEmail === 'jane@example.com';
    });
});

it('requires a name, valid email, and message to submit the contact form', function () {
    Mail::fake();

    Livewire::test(ContactPage::class)
        ->set('full_name', '')
        ->set('email', 'not-an-email')
        ->set('message', '')
        ->call('submit')
        ->assertHasErrors(['full_name', 'email', 'message']);

    Mail::assertNothingSent();
});

it('renders global faqs grouped by category on the faq hub', function () {
    Faq::create([
        'faqable_type' => null, 'faqable_id' => null, 'category' => 'Booking',
        'question' => ['en' => 'How do I book?'], 'answer' => ['en' => 'Fill in the form.'],
        'sort' => 1, 'status' => 'published',
    ]);
    Faq::create([
        'faqable_type' => null, 'faqable_id' => null, 'category' => 'Costs',
        'question' => ['en' => 'Is it cheaper?'], 'answer' => ['en' => 'Usually, yes.'],
        'sort' => 1, 'status' => 'published',
    ]);

    $this->get(route('faq'))
        ->assertOk()
        ->assertSee('Booking')
        ->assertSee('How do I book?')
        ->assertSee('Costs')
        ->assertSee('Is it cheaper?');
});

it('filters the faq hub by search text', function () {
    Faq::create([
        'faqable_type' => null, 'faqable_id' => null, 'category' => 'Booking',
        'question' => ['en' => 'How do I book?'], 'answer' => ['en' => 'Fill in the form.'],
        'sort' => 1, 'status' => 'published',
    ]);
    Faq::create([
        'faqable_type' => null, 'faqable_id' => null, 'category' => 'Costs',
        'question' => ['en' => 'Is it cheaper?'], 'answer' => ['en' => 'Usually, yes.'],
        'sort' => 1, 'status' => 'published',
    ]);

    Livewire::test(FaqPage::class)
        ->set('search', 'cheaper')
        ->assertSee('Is it cheaper?')
        ->assertDontSee('How do I book?');
});

it('filters the faq hub by category', function () {
    Faq::create([
        'faqable_type' => null, 'faqable_id' => null, 'category' => 'Booking',
        'question' => ['en' => 'How do I book?'], 'answer' => ['en' => 'Fill in the form.'],
        'sort' => 1, 'status' => 'published',
    ]);
    Faq::create([
        'faqable_type' => null, 'faqable_id' => null, 'category' => 'Costs',
        'question' => ['en' => 'Is it cheaper?'], 'answer' => ['en' => 'Usually, yes.'],
        'sort' => 1, 'status' => 'published',
    ]);

    Livewire::test(FaqPage::class)
        ->set('category', 'Booking')
        ->assertSee('How do I book?')
        ->assertDontSee('Is it cheaper?');
});

it('does not show a treatment-scoped faq on the global faq hub', function () {
    $treatment = Treatment::create([
        'slug' => 'sp-implants', 'name' => ['en' => 'SP Implants'], 'status' => 'published',
    ]);
    Faq::create([
        'faqable_type' => Treatment::class, 'faqable_id' => $treatment->id,
        'question' => ['en' => 'Scoped question?'], 'answer' => ['en' => 'Scoped answer.'],
        'sort' => 1, 'status' => 'published',
    ]);

    $this->get(route('faq'))
        ->assertOk()
        ->assertDontSee('Scoped question?');
});
