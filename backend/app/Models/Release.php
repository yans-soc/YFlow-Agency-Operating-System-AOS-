<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Release extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'release_notes',
        'released_at',
        'is_current',
        'created_by',
    ];

    protected $casts = [
        'released_at' => 'date',
        'is_current' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'created_by');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('released_at');
    }

    protected static function booted(): void
    {
        static::saving(function (Release $release) {
            if ($release->is_current) {
                static::where('is_current', true)
                    ->where('id', '!=', $release->id)
                    ->update(['is_current' => false]);
            }
        });
    }

    public function getFormattedVersionAttribute(): string
    {
        return "v{$this->version}";
    }
}