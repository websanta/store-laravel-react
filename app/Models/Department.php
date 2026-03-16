<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function scopePublished(Builder $query)
    {
        return $query->where('active', true);
    }
}
