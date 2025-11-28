<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class DomainValidationConfiguration
{
    public function __construct(
        public string $patternType,
        public bool $caseSensitive,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            patternType: (string) ($config['pattern_type'] ?? 'glob'),
            caseSensitive: (bool) ($config['case_sensitive'] ?? false),
        );
    }

    public function isValidPatternType(): bool
    {
        return in_array($this->patternType, ['glob', 'exact', 'regex'], true);
    }
}
