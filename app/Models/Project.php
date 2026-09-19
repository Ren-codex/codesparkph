<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'type',
        'summary',
        'url',
        'cover_path',
        'position',
        'is_published',
    ];

    /** @var list<string> */
    protected $appends = ['domain', 'cover_url'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'position' => 'integer',
        ];
    }

    /**
     * The bare host shown on each project card, derived from the URL so it can
     * never drift out of sync with the link itself.
     */
    protected function domain(): Attribute
    {
        return Attribute::get(fn (): string => str(
            parse_url((string) $this->url, PHP_URL_HOST) ?: ''
        )->replaceStart('www.', '')->toString());
    }

    /** Public URL of the uploaded cover, or null when the card should fall back. */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->cover_path
                ? Storage::disk('public')->url($this->cover_path)
                : null
        );
    }

    /** @param  Builder<Project>  $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /** @param  Builder<Project>  $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }
}
