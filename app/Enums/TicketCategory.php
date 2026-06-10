<?php
namespace App\Enums;

enum TicketCategory: string
{
    case Billing = 'billing';
    case Technical = 'technical';
    case Account = 'account';
    case FeatureRequest = 'feature_request';
    case Complaint = 'complaint';
    case Other = 'other';
    case Unknown = 'unknown';            // fail-safe fallback

    public static function fromLlm(?string $v): self
    {
        return self::tryFrom(strtolower(trim((string) $v))) ?? self::Unknown;
    }

    public function requiresEscalation(): bool
    {
        return in_array($this, [self::Unknown, self::Complaint], true);
    }
}