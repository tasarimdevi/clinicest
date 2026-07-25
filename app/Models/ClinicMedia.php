<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicMedia extends Model
{
    use HasFactory;

    protected $fillable = ['clinic_id', 'type', 'path', 'variants_json', 'width', 'height', 'alt', 'caption', 'is_cover', 'sort'];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
            'variants_json' => 'array',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Stored on the public disk (storage:link), so these are plain public
     * URLs — no signed/authorized download like Document's private disk.
     * `url` is the canonical JPEG fallback; `webpUrl`/`thumbUrl` are the
     * optimized variants (null for rows predating ImageProcessor, so the
     * <picture> element degrades to the JPEG).
     */
    protected function url(): Attribute
    {
        return Attribute::make(get: fn () => asset('storage/'.$this->path));
    }

    protected function webpUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => isset($this->variants_json['webp']) ? asset('storage/'.$this->variants_json['webp']) : null,
        );
    }

    protected function thumbUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => isset($this->variants_json['thumb']) ? asset('storage/'.$this->variants_json['thumb']) : $this->url,
        );
    }
}
