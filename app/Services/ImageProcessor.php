<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Central image optimizer for every upload path (clinic gallery, doctor
 * photo, before/after). Realizes the "AVIF/WebP, explicit width/height,
 * downscaled" rule from docs/03-design-system.md — a 5 MB phone photo
 * becomes a ~1600px, quality-compressed image before it ever hits the
 * disk, and (for the gallery) gets a WebP sibling + thumbnail so the
 * public page can serve modern formats via <picture>.
 *
 * Runs inline on upload rather than via a queued job (docs/08 lists image
 * processing as queue-able): variants are needed immediately for the page
 * to render, and GD on a single downscaled image is fast enough — moving
 * it onto Horizon later is a drop-in change behind this service.
 */
class ImageProcessor
{
    private const MAX_WIDTH = 1600;

    private const THUMB_WIDTH = 400;

    private const JPEG_QUALITY = 82;

    private const WEBP_QUALITY = 80;

    public function __construct(private readonly ImageManager $manager) {}

    public static function make(): self
    {
        return new self(new ImageManager(new Driver));
    }

    /**
     * Store a downscaled, compressed JPEG as the canonical file. With
     * $withVariants, also writes a full-size WebP and a WebP thumbnail.
     *
     * @return array{path: string, width: int, height: int, variants: array<string, string>}
     */
    public function storeOptimized(UploadedFile $file, string $dir, string $disk = 'public', bool $withVariants = false): array
    {
        $image = $this->manager->decode($file->getRealPath());
        $image->scaleDown(width: self::MAX_WIDTH);

        $base = trim($dir, '/').'/'.Str::uuid()->toString();
        $path = $base.'.jpg';

        Storage::disk($disk)->put($path, (string) $image->encode(new JpegEncoder(quality: self::JPEG_QUALITY)));

        $result = [
            'path' => $path,
            'width' => $image->width(),
            'height' => $image->height(),
            'variants' => [],
        ];

        if ($withVariants) {
            $webpPath = $base.'.webp';
            Storage::disk($disk)->put($webpPath, (string) $image->encode(new WebpEncoder(quality: self::WEBP_QUALITY)));

            $thumb = $this->manager->decode($file->getRealPath())->scaleDown(width: self::THUMB_WIDTH);
            $thumbPath = $base.'_thumb.webp';
            Storage::disk($disk)->put($thumbPath, (string) $thumb->encode(new WebpEncoder(quality: self::WEBP_QUALITY)));

            $result['variants'] = ['webp' => $webpPath, 'thumb' => $thumbPath];
        }

        return $result;
    }

    /**
     * Delete a stored image and all of its derived variants.
     *
     * @param  array<string, string>  $variants
     */
    public function delete(string $path, array $variants = [], string $disk = 'public'): void
    {
        Storage::disk($disk)->delete(array_values(array_filter([$path, ...array_values($variants)])));
    }
}
