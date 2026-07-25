<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Models\Clinic;
use App\Models\ClinicMedia;
use App\Services\ImageProcessor;
use Illuminate\Http\UploadedFile;

/**
 * Stored on the 'public' disk (storage/app/public, served via
 * storage:link) — unlike Document's 'local'/private disk, a clinic
 * gallery photo is meant to be publicly visible on the clinic's profile
 * page, so there's no authorization check on read, only on write
 * (ClinicPolicy::manage(), checked by the caller). The first photo a
 * clinic uploads becomes its cover automatically; after that, cover
 * changes are explicit (see ClinicProfile::setCoverMedia()).
 *
 * The raw upload is run through ImageProcessor first (downscaled +
 * compressed JPEG, plus WebP + thumbnail variants) — the gallery is the
 * most image-heavy public surface, so it gets the full <picture> pipeline.
 */
class UploadClinicMedia
{
    public function __construct(private readonly ImageProcessor $processor) {}

    public function handle(Clinic $clinic, UploadedFile $file, ?string $caption = null): ClinicMedia
    {
        $optimized = $this->processor->storeOptimized($file, "clinic-media/{$clinic->id}", 'public', withVariants: true);

        $nextSort = ((int) $clinic->media()->max('sort')) + 1;

        return $clinic->media()->create([
            'type' => 'image',
            'path' => $optimized['path'],
            'variants_json' => $optimized['variants'],
            'width' => $optimized['width'],
            'height' => $optimized['height'],
            'alt' => $caption,
            'caption' => $caption,
            'is_cover' => ! $clinic->media()->exists(),
            'sort' => $nextSort,
        ]);
    }
}
