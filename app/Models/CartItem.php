<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $casts = [
        'variation_type_option_ids' => 'array',
    ];

    // Fix for comparison with an empty array in the case of no variations for a product
    public function scopeWhereOptionIds($query, $optionIds)
    {
        if (empty($optionIds)) {
            return $query->where(function ($q) {
                $q->whereNull('variation_type_option_ids')
                    ->orWhereRaw('variation_type_option_ids::jsonb = \'[]\'::jsonb')
                    ->orWhereRaw('variation_type_option_ids::jsonb = \'{}\'::jsonb');
            });
        }

        return $query->whereRaw('variation_type_option_ids::jsonb = ?::jsonb', [json_encode($optionIds)]);
    }
}
