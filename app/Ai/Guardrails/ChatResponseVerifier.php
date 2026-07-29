<?php

declare(strict_types=1);

namespace App\Ai\Guardrails;

/**
 * Mechanical check run AFTER the model has generated a full response and
 * BEFORE it is shown to the user or saved as replayable history. Prompt
 * instructions alone don't stop a model from inventing a price, so every
 * currency-tagged number in the draft is cross-checked against the actual
 * figures returned by this turn's tool calls (e.g. get_cost_estimate); any
 * number that doesn't match a verified figure gets the whole reply replaced
 * with a safe fallback. The rejected draft is never fed back to the model as
 * conversation history — see chat_messages.original_draft, audit-only.
 */
class ChatResponseVerifier
{
    private const CURRENCY_PATTERN = '/(?:€|\$|₺|£|\bEUR\b|\bUSD\b|\bTRY\b|\bTL\b)\s?\d[\d.,]*\d?|\d[\d.,]*\d?\s?(?:€|\$|₺|£|\bEUR\b|\bUSD\b|\bTRY\b|\bTL\b)/iu';

    /**
     * @param  array<int, float>  $verifiedAmounts  numbers actually returned by tool calls this turn
     * @return array{content: string, flagged: bool, flag_reason: ?string}
     */
    public function verify(string $draft, array $verifiedAmounts = []): array
    {
        preg_match_all(self::CURRENCY_PATTERN, $draft, $matches);
        $claims = $matches[0] ?? [];

        foreach ($claims as $claim) {
            if (! in_array($this->normalizeNumber($claim), $verifiedAmounts, true)) {
                return [
                    'content' => self::fallback(),
                    'flagged' => true,
                    'flag_reason' => "unverified price claim: {$claim}",
                ];
            }
        }

        return ['content' => $draft, 'flagged' => false, 'flag_reason' => null];
    }

    public static function fallback(): string
    {
        return 'Kesin bir tutar paylaşamam, ama size özel gerçek bir fiyat aralığı için '
            .'AI Maliyet Hesaplayıcı\'yı deneyebilir ya da Ücretsiz Teklif Al formunu '
            .'doldurabilirsiniz — klinikler size özel net bir teklif sunar.';
    }

    private function normalizeNumber(string $claim): float
    {
        $digits = preg_replace('/[^\d.,]/', '', $claim) ?? '';
        // Treat a '.' or ',' as a thousands separator when followed by
        // exactly 3 digits then a non-digit/end; otherwise it's a decimal.
        $noThousands = preg_replace('/[.,](?=\d{3}(?:\D|$))/', '', $digits) ?? $digits;

        return (float) str_replace(',', '.', $noThousands);
    }
}
