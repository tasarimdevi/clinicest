<?php

declare(strict_types=1);

it('renders the homepage successfully', function () {
    $this->get('/')->assertOk();
});

it('renders key public pages', function () {
    $this->get('/treatments')->assertOk();
    $this->get('/clinics')->assertOk();
    $this->get('/doctors')->assertOk();
    $this->get('/get-quote')->assertOk();
});
