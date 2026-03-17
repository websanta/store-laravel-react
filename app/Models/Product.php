<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use App\Enums\VendorStatusEnum;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100);
        $this->addMediaConversion('small')
            ->width(480);
        $this->addMediaConversion('large')
            ->width(1200);
    }

    public function scopeForVendor(Builder $query): Builder
    {
        return $query->where('created_by', Auth::id());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('products.status', ProductStatusEnum::Published);
    }

    public function scopeForWebsite(Builder $query): Builder
    {
        return $query->published()->vendorApproved();
    }

    public function scopeVendorApproved(Builder $query): Builder
    {
        return $query->whereHas('user.vendor', function ($q) {
            $q->where('status', VendorStatusEnum::Approved->value);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variationTypes(): HasMany
    {
        return $this->hasMany(VariationTypes::class);
    }

    public function options(): HasManyThrough
    {
        return $this->hasManyThrough(
            VariationTypeOption::class, // Target model
            VariationTypes::class, // Intermediate model
            'product_id', // Foreign key on VariationType table
            'variation_type_id', // Foreign key on Option table
            'id', // Local key on Product table
            'id' // Local key on VariationType table
        );
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    public function getPriceForOptions($optionIds = [])
    {
        $optionIds = array_values($optionIds);
        sort($optionIds);
        foreach ($this->variations as $variation) {
            $a = $variation->variation_type_option_ids;
            sort($a);
            if ($optionIds == $a) {
                return $variation->price !== null ? $variation->price : $this->price;
            }
        }

        return $this->price;
    }

    public function getImageForOptions($optionIds = null)
    {
        if ($optionIds) {
            $optionIds = array_values($optionIds);
            sort($optionIds);
            $options = VariationTypeOption::whereIn('id', $optionIds)->get();
            foreach ($options as $option) {
                $image = $option->getFirstMediaUrl('images', 'small');
                if ($image) {
                    return $image;
                }
            }
        }

        return $this->getFirstMediaUrl('images', 'small');
    }

    public function getImagesForOptions($optionIds = null)
    {
        if ($optionIds) {
            $optionIds = array_values($optionIds);
            $options = VariationTypeOption::whereIn('id', $optionIds)->get();
            foreach ($options as $option) {
                $images = $option->getMedia('images');
                if ($images) {
                    return $images;
                }
            }
        }

        return $this->getFirstMediaUrl('images', 'small');
    }

    public function getPriceForFirstOptions(): float
    {
        $firstOptions = $this->getFirstOptionsMap();

        if ($firstOptions) {
            return $this->getPriceForOptions($firstOptions);
        }
        return $this->price;
    }

    public function getFirstImageUrl($collectionName = 'images', $conversion = 'small')
    {
        if ($this->options->count() > 0) {
            foreach ($this->options as $option) {
                $imageUrl = $option->getFirstMediaUrl($collectionName, $conversion);
                if ($imageUrl) {
                    return $imageUrl;
                }
            }
            return $this->options->first()->getFirstMediaUrl($collectionName, $conversion);
        }
        return $this->getFirstMediaUrl($collectionName, $conversion);
    }

    public function getImages()
    {
        if ($this->options->count() > 0) {
            foreach ($this->options as $option) {
                $images = $option->getMedia('images');
                if ($images) {
                    return $images;
                }
            }
        }
        return $this->getMedia('images');
    }

    public function getFirstOptionsMap(): array
    {
        return $this->variationTypes
            ->mapWithKeys(fn($type) => [$type->id => $type->options[0]?->id])
            ->toArray();
    }
}
