<?php

declare(strict_types=1);

namespace App\Ai\Support;

/**
 * Masks patient-typed PII before it leaves this server for Groq. Applied to
 * the 'user' role messages in the replayed history on every turn — chat
 * completion APIs are stateless, so the whole conversation goes out again
 * each time, meaning a phone number typed three turns ago must still be
 * masked on turn four.
 */
class PiiRedactor
{
    public static function redact(string $text): string
    {
        $text = preg_replace('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/', '[e-posta gizlendi]', $text);
        // Turkish national ID (TCKN): exactly 11 digits, first digit non-zero.
        $text = preg_replace('/\b[1-9]\d{10}\b/', '[kimlik no gizlendi]', $text);
        // Remaining phone-shaped digit runs (with optional spaces/dashes/parens).
        $text = preg_replace('/(?:\+?\d[\d\s\-\(\)]{7,}\d)/', '[telefon gizlendi]', $text);

        return $text;
    }

    /**
     * @param  array<int, array{role: string, content: ?string}>  $messages
     * @return array<int, array{role: string, content: ?string}>
     */
    public static function redactMessages(array $messages): array
    {
        return array_map(static function (array $message): array {
            if (($message['role'] ?? null) === 'user' && is_string($message['content'] ?? null)) {
                $message['content'] = self::redact($message['content']);
            }

            return $message;
        }, $messages);
    }
}
