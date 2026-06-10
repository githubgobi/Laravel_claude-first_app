<?php
namespace App\Services;

class InjectionGuard
{
    private array $patterns = [
        '/ignore\s+(all\s+)?previous\s+instructions/i',
        '/disregard\s+the\s+above/i',
        '/system\s*prompt/i',
        '/you\s+are\s+now/i',
        '/reveal\s+your\s+(instructions|prompt)/i',
    ];

    public function looksLikeInjection(string $in): bool
    {
        foreach ($this->patterns as $p) {
            if (preg_match($p, $in)) return true;
        }
        return false;
    }

    public function stripLeakedKeys(array $out): array
    {
        return array_intersect_key($out, array_flip(['category', 'confidence', 'reason']));
    }
}