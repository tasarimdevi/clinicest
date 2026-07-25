<?php

declare(strict_types=1);

it('renders the privacy policy page with a draft notice and grounded content', function () {
    $this->get(route('legal.privacy'))
        ->assertOk()
        ->assertSee('Privacy Policy')
        ->assertSee('Draft for review')
        ->assertSee('Who we share it with')
        ->assertSee('Turkey');
});

it('renders the terms of service page with a draft notice', function () {
    $this->get(route('legal.terms'))
        ->assertOk()
        ->assertSee('Terms of Service')
        ->assertSee('Draft for review')
        ->assertSee('Not medical advice');
});

it('renders the gdpr page with rights and a contact path', function () {
    $this->get(route('legal.gdpr'))
        ->assertOk()
        ->assertSee('GDPR')
        ->assertSee('Right to erasure', false)
        ->assertSee(config('clinicest.contact_email'));
});

it('links between the privacy policy and gdpr pages', function () {
    $this->get(route('legal.privacy'))
        ->assertOk()
        ->assertSee('href="'.route('legal.gdpr').'"', false);

    $this->get(route('legal.gdpr'))
        ->assertOk()
        ->assertSee('href="'.route('legal.privacy').'"', false);
});

it('links to the legal pages from the public footer', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('href="'.route('legal.privacy').'"', false)
        ->assertSee('href="'.route('legal.terms').'"', false)
        ->assertSee('href="'.route('legal.gdpr').'"', false);
});
