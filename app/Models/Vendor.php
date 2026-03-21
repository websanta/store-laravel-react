<?php

namespace App\Models;

use App\Enums\VendorStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Vendor extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_id',
        'status',
        'store_name',
        'store_address',
        'cover_image',
        'rejection_reason',
        'verified_at'
    ];

    protected $casts = [
        'status' => VendorStatusEnum::class,
        'verified_at' => 'datetime',
    ];

    public function scopeEligibleForPayout(Builder $query): Builder
    {
        return $query->where('status', VendorStatusEnum::Approved);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', VendorStatusEnum::Pending);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', VendorStatusEnum::Approved);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', VendorStatusEnum::Rejected);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'created_by', 'user_id');
    }

    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(
            Order::class,
            Product::class,
            'created_by', // Foreign key on products table
            'id', // Foreign key on orders table
            'user_id', // Local key on vendors table
            'id' // Local key on products table
        )->where('orders.vendor_user_id', $this->user_id);
    }

    public function approve(): void
    {
        $this->update([
            'status' => VendorStatusEnum::Approved,
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(string $reason): void
    {
        $this->update([
            'status' => VendorStatusEnum::Rejected,
            'rejection_reason' => $reason,
            'verified_at' => null,
        ]);
    }

    public function isApproved(): bool
    {
        return $this->status === VendorStatusEnum::Approved;
    }

    public function isPending(): bool
    {
        return $this->status === VendorStatusEnum::Pending;
    }

    public function isRejected(): bool
    {
        return $this->status === VendorStatusEnum::Rejected;
    }
}
