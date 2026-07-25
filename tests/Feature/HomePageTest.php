<?php

declare(strict_types=1);

it('redirects the bare root to the default locale home', function () {
    $this->get('/')->assertRedirect(route('home', ['locale' => 'en']));
});

it('renders key public pages under the locale prefix', function () {
    $this->get(route('home'))->assertOk();            // /en
    $this->get(route('treatments.index'))->assertOk(); // /en/treatments
    $this->get(route('clinics.index'))->assertOk();
    $this->get(route('doctors.index'))->assertOk();
    $this->get(route('get-quote'))->assertOk();
});
