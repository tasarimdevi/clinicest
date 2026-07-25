<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // URL::defaults lives on the UrlGenerator singleton and persists
        // across tests in one process — a prior test's SetLocale (which sets
        // the {locale} URL default) would otherwise leak into the next
        // test's route() calls. Reset it to the default locale so route()
        // works in tests that generate URLs before making a request.
        URL::defaults(['locale' => config('clinicest.locales.default', 'en')]);
    }
}
