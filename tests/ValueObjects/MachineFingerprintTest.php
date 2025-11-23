<?php

declare(strict_types=1);
use Akira\LaravelLicense\ValueObjects\MachineFingerprint;

it('creates with hash', function () {
    expect((new MachineFingerprint('hash123'))->hash)->toBe('hash123');
});

it('creates from raw', function () {
    expect(MachineFingerprint::fromRaw('raw')->hash)->toBe(hash('sha256', 'raw'));
});

it('returns null for null', function () {
    expect(MachineFingerprint::fromRaw(null))->toBeNull();
});

it('returns null for empty', function () {
    expect(MachineFingerprint::fromRaw(''))->toBeNull();
});

it('consistent hash', function () {
    expect(MachineFingerprint::fromRaw('test')->hash)->toBe(MachineFingerprint::fromRaw('test')->hash);
});

it('different hash', function () {
    expect(MachineFingerprint::fromRaw('a')->hash)->not->toBe(MachineFingerprint::fromRaw('b')->hash);
});

it('sha256 length', function () {
    expect(MachineFingerprint::fromRaw('test')->hash)->toHaveLength(64);
});

it('is readonly', function () {
    $fp = new MachineFingerprint('h');
    expect(fn () => $fp->hash = 'new')->toThrow(Error::class);
});
