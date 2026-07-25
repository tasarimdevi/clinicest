<?php

declare(strict_types=1);

use App\Services\ImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('downscales an oversized image and reports its new dimensions', function () {
    Storage::fake('public');

    $out = ImageProcessor::make()->storeOptimized(
        UploadedFile::fake()->image('huge.jpg', 4000, 2000),
        'test',
        'public'
    );

    expect($out['width'])->toBe(1600);
    expect($out['height'])->toBe(800);
    expect($out['path'])->toEndWith('.jpg');
    Storage::disk('public')->assertExists($out['path']);
});

it('does not upscale an image that is already small', function () {
    Storage::fake('public');

    $out = ImageProcessor::make()->storeOptimized(
        UploadedFile::fake()->image('small.jpg', 300, 200),
        'test',
        'public'
    );

    expect($out['width'])->toBe(300);
    expect($out['height'])->toBe(200);
});

it('generates webp and thumbnail variants only when asked', function () {
    Storage::fake('public');
    $processor = ImageProcessor::make();

    $plain = $processor->storeOptimized(UploadedFile::fake()->image('a.jpg', 800, 600), 'test', 'public', withVariants: false);
    expect($plain['variants'])->toBe([]);

    $rich = $processor->storeOptimized(UploadedFile::fake()->image('b.jpg', 800, 600), 'test', 'public', withVariants: true);
    expect($rich['variants'])->toHaveKeys(['webp', 'thumb']);
    Storage::disk('public')->assertExists($rich['variants']['webp']);
    Storage::disk('public')->assertExists($rich['variants']['thumb']);
});

it('deletes an image together with all of its variants', function () {
    Storage::fake('public');
    $processor = ImageProcessor::make();

    $out = $processor->storeOptimized(UploadedFile::fake()->image('c.jpg', 800, 600), 'test', 'public', withVariants: true);

    $processor->delete($out['path'], $out['variants']);

    Storage::disk('public')->assertMissing($out['path']);
    Storage::disk('public')->assertMissing($out['variants']['webp']);
    Storage::disk('public')->assertMissing($out['variants']['thumb']);
});
