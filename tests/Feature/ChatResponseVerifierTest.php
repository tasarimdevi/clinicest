<?php

declare(strict_types=1);

use App\Ai\Guardrails\ChatResponseVerifier;

it('rejects a currency-tagged number that was not returned by any tool call', function () {
    $result = (new ChatResponseVerifier)->verify(
        'This treatment costs €2500 in Turkey.',
        verifiedAmounts: [1800.0, 2200.0],
    );

    expect($result['flagged'])->toBeTrue();
    expect($result['content'])->toBe(ChatResponseVerifier::fallback());
    expect($result['flag_reason'])->toContain('2500');
});

it('accepts a currency-tagged number that matches a verified tool-call amount', function () {
    $result = (new ChatResponseVerifier)->verify(
        'This treatment costs €2200 in Turkey.',
        verifiedAmounts: [1800.0, 2200.0],
    );

    expect($result['flagged'])->toBeFalse();
    expect($result['content'])->toBe('This treatment costs €2200 in Turkey.');
});

it('does not flag responses with no currency-tagged numbers at all', function () {
    $result = (new ChatResponseVerifier)->verify('We have 3 verified clinics for this treatment.');

    expect($result['flagged'])->toBeFalse();
    expect($result['content'])->toBe('We have 3 verified clinics for this treatment.');
});
