<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'store_name' => $this->vendor->store_name,
            'store_address' => $this->vendor->store_address,
            'vendor_status' => $this->vendor->status->value,
            'vendor_status_label' => $this->vendor->status->label(),
            'verified_at' => $this->vendor->verified_at,
            'rejection_reason' => $this->vendor->rejection_reason,
        ];
    }
}
