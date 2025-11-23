<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\DomainName;

it('can create domain name with host', function () {
    $domain = new DomainName('example.com');

    expect($domain->host)->toBe('example.com');
});

it('can create from url', function () {
    $domain = DomainName::fromUrlOrHost('https://example.com/path');

    expect($domain)->toBeInstanceOf(DomainName::class)
        ->and($domain->host)->toBe('example.com');
});

it('can create from host only', function () {
    $domain = DomainName::fromUrlOrHost('example.com');

    expect($domain)->toBeInstanceOf(DomainName::class)
        ->and($domain->host)
        ->toBe('example.com');
});

it('returns null for null input', function () {
    $domain = DomainName::fromUrlOrHost(null);

    expect($domain)->toBeNull();
});

it('returns null for empty string', function () {
    $domain = DomainName::fromUrlOrHost('');

    expect($domain)->toBeNull();
});

it('extracts host from full url with scheme', function () {
    $domain = DomainName::fromUrlOrHost('https://www.example.com:8080/path?query=1');

    expect($domain->host)->toBe('www.example.com');
});

it('extracts host from http url', function () {
    $domain = DomainName::fromUrlOrHost('http://example.com');

    expect($domain->host)->toBe('example.com');
});

it('handles subdomain', function () {
    $domain = DomainName::fromUrlOrHost('sub.example.com');

    expect($domain->host)->toBe('sub.example.com');
});

it('handles localhost', function () {
    $domain = DomainName::fromUrlOrHost('localhost');

    expect($domain->host)->toBe('localhost');
});

it('handles ip address', function () {
    $domain = DomainName::fromUrlOrHost('192.168.1.1');

    expect($domain->host)->toBe('192.168.1.1');
});

it('is readonly', function () {
    $domain = new DomainName('example.com');

    expect(fn () => $domain->host = 'other.com')
        ->toThrow(Error::class);
});

it('handles malformed url', function () {
    $domain = DomainName::fromUrlOrHost('http:///invalid');

    expect($domain)->toBeNull();
});

it('handles url with only scheme', function () {
    $domain = DomainName::fromUrlOrHost('http://');

    expect($domain)->toBeNull();
});

it('handles domain with port as plain string', function () {
    $domain = DomainName::fromUrlOrHost('example.com:8080');

    expect($domain->host)->toBe('example.com');
});

it('handles ftp scheme', function () {
    $domain = DomainName::fromUrlOrHost('ftp://files.example.com');

    expect($domain->host)->toBe('files.example.com');
});

it('handles url with authentication', function () {
    $domain = DomainName::fromUrlOrHost('https://user:pass@example.com/path');

    expect($domain->host)->toBe('example.com');
});

it('handles ipv6 address', function () {
    $domain = DomainName::fromUrlOrHost('[2001:db8::1]');

    expect($domain->host)->toBe('[2001:db8::1]');
});

it('handles whitespace only', function () {
    $domain = DomainName::fromUrlOrHost('   ');

    expect($domain->host)->toBe('   ');
});

it('handles url with fragment', function () {
    $domain = DomainName::fromUrlOrHost('https://example.com/page#section');

    expect($domain->host)->toBe('example.com');
});
