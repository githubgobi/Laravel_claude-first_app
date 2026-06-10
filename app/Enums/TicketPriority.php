<?php

namespace App\Enums;

enum TicketPriority: string
{
    case High   = 'high';
    case Medium = 'medium';
    case Low    = 'low';

    public static function fromLlm(?string $v): self
    {
        return self::tryFrom(strtolower(trim((string) $v))) ?? self::Medium;
    }
}
