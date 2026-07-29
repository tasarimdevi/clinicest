<?php

declare(strict_types=1);

use App\Ai\Support\PiiRedactor;

it('masks an email address', function () {
    expect(PiiRedactor::redact('My email is jane@example.com, call me back.'))
        ->toBe('My email is [e-posta gizlendi], call me back.');
});

it('masks a turkish national id number', function () {
    expect(PiiRedactor::redact('My TCKN is 12345678901.'))
        ->toBe('My TCKN is [kimlik no gizlendi].');
});

it('masks a phone number', function () {
    expect(PiiRedactor::redact('Call me at +90 555 123 45 67 please.'))
        ->toContain('[telefon gizlendi]');
});

it('only redacts user-authored messages when redacting a conversation history', function () {
    $redacted = PiiRedactor::redactMessages([
        ['role' => 'system', 'content' => 'contact: admin@clinicest.com'],
        ['role' => 'user', 'content' => 'my email is jane@example.com'],
    ]);

    expect($redacted[0]['content'])->toBe('contact: admin@clinicest.com');
    expect($redacted[1]['content'])->toBe('my email is [e-posta gizlendi]');
});
