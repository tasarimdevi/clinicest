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

    protected $fillable = ['clinic_id', 'type', 'path', 'alt', 'caption', 'is_cover', 'sort'];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Stored on the public disk (storage:link), so this is a plain public
     * URL — no signed/authorized download like Document's private disk.
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => asset('storage/'.$this->path),
        );
    }
}
