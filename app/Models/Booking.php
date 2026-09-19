<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public const STATUS_NEW = 'new';

    /** Stages a request moves through once it lands in the inbox. */
    public const STATUSES = [
        self::STATUS_NEW,
        'contacted',
        'scheduled',
        'completed',
        'declined',
    ];

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'service',
        'budget',
        'details',
        'status',
        'scheduled_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    /** @param  Builder<Booking>  $query */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNotIn('status', ['completed', 'declined']);
    }
}
