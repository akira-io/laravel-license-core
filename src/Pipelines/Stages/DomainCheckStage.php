<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Exceptions\DomainBlockedException;
use Akira\LaravelLicense\Exceptions\DomainNotAllowedException;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\ValueObjects\DomainValidationConfiguration;
use Akira\LaravelLicense\ValueObjects\LicenseContext;

final readonly class DomainCheckStage implements LicenseValidatorStage
{
    public function __construct(private DomainValidationConfiguration $domainConfig) {}

    public function __invoke(LicenseContext $context): LicenseContext
    {
        $license = $context->license ?? throw LicenseNotLoadedException::create();

        if (! $context->domain) {
            return $context;
        }

        /** @var array<string, mixed> $meta */
        $meta = $license->meta ?? [];
        /** @var array<int, string> $allowed */
        $allowed = $meta['allowed_domains'] ?? [];
        /** @var array<int, string> $blocked */
        $blocked = $meta['blocked_domains'] ?? [];
        $domain = $context->domain->host;

        foreach ($blocked as $pattern) {
            if ($this->matches($domain, $pattern)) {
                throw DomainBlockedException::forDomain($domain);
            }
        }

        if ($allowed === []) {
            return $context;
        }

        foreach ($allowed as $pattern) {
            if ($this->matches($domain, $pattern)) {
                return $context;
            }
        }

        throw DomainNotAllowedException::forDomain($domain);
    }

    private function matches(string $domain, string $pattern): bool
    {
        $flags = $this->domainConfig->caseSensitive ? '' : 'i';

        return match ($this->domainConfig->patternType) {
            'exact' => $this->domainConfig->caseSensitive
                ? $domain === $pattern
                : mb_strtolower($domain) === mb_strtolower($pattern),
            'glob' => (bool) preg_match(
                '/^'.str_replace('\*', '.*', preg_quote($pattern, '/')).'$/'.$flags,
                $domain
            ),
            'regex' => (bool) preg_match('/'.$pattern.'/'.$flags, $domain),
            default => false,
        };
    }
}
