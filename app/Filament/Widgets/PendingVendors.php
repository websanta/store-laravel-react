<?php

namespace App\Filament\Widgets;

use App\Enums\RolesEnum;
use App\Enums\PermissionsEnum;
use App\Filament\Resources\VendorResource;
use App\Models\Vendor;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Infolists;

class PendingVendors extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'New vendor requests';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user?->hasRole(RolesEnum::Admin->value) ||
            $user?->can(PermissionsEnum::ApproveVendors->value);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vendor::query()
                    ->pending()
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Vendor'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('store_name')
                    ->label('Store'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Request date'),
            ]);
    }
}
