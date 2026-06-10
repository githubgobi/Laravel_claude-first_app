<?php

namespace App\Enums;

enum TicketSentiment: string
{
    case Positive = 'positive';
    case Neutral  = 'neutral';
    case Negative = 'negative';
    case Angry    = 'angry';

    public static function fromLlm(?string $v): self
    {
        return self::tryFrom(strtolower(trim((string) $v))) ?? self::Neutral;
    }
}
