<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Enums\VendorStatusEnum;
use App\Filament\Resources\VendorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn(Builder $query) => $query->pending())
                ->badge(fn() => VendorResource::getModel()::pending()->count())
                ->badgeColor('warning'),
            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn(Builder $query) => $query->approved())
                ->badge(fn() => VendorResource::getModel()::approved()->count())
                ->badgeColor('success'),
            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn(Builder $query) => $query->rejected())
                ->badge(fn() => VendorResource::getModel()::rejected()->count())
                ->badgeColor('danger'),
        ];
    }
}
