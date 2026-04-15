<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    /** @use HasFactory<\Database\Factories\PlanFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'stripe_price_id',
        'price_monthly',
        'price_yearly',
        'features',
        'is_popular',
    ];

    protected $casts = [
        'features'   => 'array',
        'is_popular' => 'boolean',
    ];

    public function getMonthlyPriceFormattedAttribute(): string
    {
        return '$' . number_format($this->price_monthly / 100, 0);
    }

    public function getYearlyPriceFormattedAttribute(): string
    {
        return '$' . number_format($this->price_yearly / 100, 0);
    }
}
