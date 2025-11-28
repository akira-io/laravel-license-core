<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class DomainValidationConfiguration
{
    public function __construct(
        public string $patternType,
        public bool $caseSensitive,
    ) {}

    public function isValidPatternType(): bool
    {
        return in_array($this->patternType, ['glob', 'exact', 'regex'], true);
    }
}
