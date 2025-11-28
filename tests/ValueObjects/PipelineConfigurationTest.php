<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\PipelineConfiguration;

test('creates pipeline configuration from constructor', function () {
    $config = new PipelineConfiguration(
        usageStages: [
            'resolve_license',
            'status_check',
            'expiration_usage',
        ],
        updateStages: [
            'resolve_license',
            'status_check',
            'update_window',
        ],
    );

    expect($config->usageStages)->toBe([
        'resolve_license',
        'status_check',
        'expiration_usage',
    ])->and($config->updateStages)->toBe([
        'resolve_license',
        'status_check',
        'update_window',
    ]);
});

test('uses default usage stages when not provided', function () {
    $config = new PipelineConfiguration(
        usageStages: [
            'resolve_license',
            'status_check',
            'expiration_usage',
            'grace_period',
            'domain_check',
            'machine_check',
            'credits_usage',
            'abuse_heuristics',
        ],
        updateStages: [
            'resolve_license',
        ],
    );

    expect($config->usageStages)->toContain('resolve_license')
        ->and($config->usageStages)->toContain('status_check')
        ->and($config->usageStages)->toContain('abuse_heuristics');
});

test('uses default update stages when not provided', function () {
    $config = new PipelineConfiguration(
        usageStages: [
            'resolve_license',
        ],
        updateStages: [
            'resolve_license',
            'status_check',
            'expiration_usage',
            'grace_period',
            'update_window',
        ],
    );

    expect($config->updateStages)->toContain('resolve_license')
        ->and($config->updateStages)->toContain('status_check')
        ->and($config->updateStages)->toContain('update_window');
});

test('allows custom usage pipeline', function () {
    $config = new PipelineConfiguration(
        usageStages: [
            'custom_stage_1',
            'custom_stage_2',
            'custom_stage_3',
        ],
        updateStages: [
            'resolve_license',
            'status_check',
            'update_window',
        ],
    );

    expect($config->usageStages)->toBe([
        'custom_stage_1',
        'custom_stage_2',
        'custom_stage_3',
    ]);
});

test('allows custom update pipeline', function () {
    $config = new PipelineConfiguration(
        usageStages: [
            'resolve_license',
            'status_check',
            'expiration_usage',
        ],
        updateStages: [
            'custom_stage_1',
            'custom_stage_2',
        ],
    );

    expect($config->updateStages)->toBe([
        'custom_stage_1',
        'custom_stage_2',
    ]);
});

test('allows removing stages from usage pipeline', function () {
    $config = new PipelineConfiguration(
        usageStages: [
            'resolve_license',
            'status_check',
        ],
        updateStages: [
            'resolve_license',
            'status_check',
            'update_window',
        ],
    );

    expect($config->usageStages)->toHaveLength(2)
        ->and($config->usageStages)->not->toContain('abuse_heuristics');
});

test('allows removing stages from update pipeline', function () {
    $config = new PipelineConfiguration(
        usageStages: [
            'resolve_license',
            'status_check',
            'expiration_usage',
        ],
        updateStages: [
            'resolve_license',
        ],
    );

    expect($config->updateStages)->toHaveLength(1)
        ->and($config->updateStages)->not->toContain('update_window');
});

test('usage stages is a list', function () {
    $config = new PipelineConfiguration(
        usageStages: [
            'stage_1',
            'stage_2',
            'stage_3',
        ],
        updateStages: [
            'resolve_license',
        ],
    );

    expect($config->usageStages)->toBeArray();
});

test('update stages is a list', function () {
    $config = new PipelineConfiguration(
        usageStages: [
            'resolve_license',
        ],
        updateStages: [
            'stage_1',
            'stage_2',
        ],
    );

    expect($config->updateStages)->toBeArray();
});
