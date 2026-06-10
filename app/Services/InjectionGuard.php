<?php

namespace App\Services;

class InjectionGuard
{
    // Expanded from 5 → 8 patterns (Lesson1 hardened set)
    private const PATTERNS = [
        '/ignore\s+(all\s+)?(previous|prior|above)\s+(instructions|rules)/i',
        '/forget\s+(you\s+are|your\s+(instructions|role|rules))/i',
        '/you\s+are\s+now\b/i',
        '/(set|mark|classify|treat)\s+(this\s+)?(as\s+)?(priority|category|sentiment)\s*[:=]/i',
        '/(priority|sentiment)\s*[:=]\s*(low|positive)/i',
        '/\[?\s*system\s*(note|prompt|message|instruction)?\s*[:\]]/i',
        '/do\s+not\s+output\s+json/i',
        '/repeat\s+(your|the)\s+(instructions|system|prompt)/i',
    ];

    // Layer 3: phrases that indicate the model leaked the system prompt into output
    private const LEAK_SIGNATURES = [
        'you are', 'support ticket classifier', 'allowed values',
        'json schema', 'classify the following', 'system prompt',
        'your instructions', 'security rules', 'never overridden',
        'never obey', 'untrusted customer text',
    ];

    public function looksLikeInjection(string $in): bool
    {
        foreach (self::PATTERNS as $p) {
            if (preg_match($p, $in)) return true;
        }
        return false;
    }

    // Layer 3: redact freeform fields that contain system-prompt fragments
    public function sanitizeText(string $s, int $max = 200): string
    {
        $s     = trim($s);
        $lower = strtolower($s);
        foreach (self::LEAK_SIGNATURES as $sig) {
            if (str_contains($lower, $sig)) {
                return '[redacted: possible instruction leak]';
            }
        }
        return mb_strlen($s) > $max ? mb_substr($s, 0, $max) . '…' : $s;
    }
}
