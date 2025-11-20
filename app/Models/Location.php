<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{

    use HasFactory;

    protected $fillable = [
        'name',
        'svg_map',
        'places_total',
    ];

    protected $casts = [
        'places_total' => 'integer',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
