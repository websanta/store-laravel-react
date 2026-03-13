<?php

namespace App\Filament\Resources;

use App\Enums\PermissionsEnum;
use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use App\Filament\Resources\VendorResource\Pages;
use App\Mail\VendorStatusChanged;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Vendors';

    protected static ?string $modelLabel = 'Vendor';

    protected static ?string $pluralModelLabel = 'Vendors';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->hasRole(RolesEnum::Admin->value) ||
            $user?->can(PermissionsEnum::ApproveVendors->value);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        return $user?->hasRole(RolesEnum::Admin->value) ||
            $user?->can(PermissionsEnum::ApproveVendors->value);
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();
        return $user?->hasRole(RolesEnum::Admin->value);
    }

    public static function canView(Model $record): bool
    {
        $user = auth()->user();
        return $user?->hasRole(RolesEnum::Admin->value) ||
            $user?->can(PermissionsEnum::ApproveVendors->value);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Vendor information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(false)
                            ->label('Vendor'),
                        Forms\Components\TextInput::make('store_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Store name'),
                        Forms\Components\Textarea::make('store_address')
                            ->maxLength(255)
                            ->label('Store address'),
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->directory('vendors/covers')
                            ->label('Store cover'),
                    ])->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(VendorStatusEnum::labels())
                            ->required()
                            ->native(false)
                            ->label('Status'),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->visible(fn($get) => $get('status') === VendorStatusEnum::Rejected->value)
                            ->required(fn($get) => $get('status') === VendorStatusEnum::Rejected->value)
                            ->label('Rejection reason')
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label('Verification date'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->sortable()
                    ->label('ID'),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Vendor'),
                Tables\Columns\TextColumn::make('user.email')
                    ->searchable()
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('store_name')
                    ->searchable()
                    ->sortable()
                    ->label('Store name'),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(VendorStatusEnum $state): string => match ($state) {
                        VendorStatusEnum::Pending => 'warning',
                        VendorStatusEnum::Approved => 'success',
                        VendorStatusEnum::Rejected => 'danger',
                    })
                    ->formatStateUsing(fn(VendorStatusEnum $state): string => $state->label())
                    ->label('Статус'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Registration date'),
                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Verification date')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(VendorStatusEnum::labels())
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn(Vendor $record): bool => $record->isPending())
                    ->action(function (Vendor $record) {
                        $record->approve();

                        // Optional: send notification
                        Mail::to($record->user->email)->send(new VendorStatusChanged($record, 'approved'));

                        Notification::make()
                            ->title('Vendor approved')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Approve vendor')
                    ->modalDescription('Are you sure you want to approve this vendor?'),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn(Vendor $record): bool => $record->isPending())
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->label('Rejection reason')
                            ->placeholder('Specify rejection reason'),
                    ])
                    ->action(function (array $data, Vendor $record) {
                        $record->reject($data['rejection_reason']);

                        // Optional: send notification
                        Mail::to($record->user->email)->send(new VendorStatusChanged($record, 'rejected', $data['rejection_reason']));

                        Notification::make()
                            ->title('Vendor rejected')
                            ->danger()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Reject vendor')
                    ->modalDescription('Are you sure you want to reject this vendor?'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    // Bulk action for mass approval
                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Approve selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->isPending()) {
                                    $record->approve();
                                }
                            });

                            Notification::make()
                                ->title('Chosen vendors approved')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Vendor information')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Vendor'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('store_name')
                            ->label('Store name'),
                        Infolists\Components\TextEntry::make('store_address')
                            ->label('Store address'),
                        Infolists\Components\ImageEntry::make('cover_image')
                            ->label('Store cover')
                            ->visible(fn($record) => !is_null($record->cover_image))
                            ->width(200)
                            ->height(200),
                    ])->columns(2),

                Infolists\Components\Section::make('Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn(Vendor $record): string => match ($record->status) {
                                VendorStatusEnum::Approved => 'success',
                                VendorStatusEnum::Pending => 'warning',
                                VendorStatusEnum::Rejected => 'danger',
                            })
                            ->formatStateUsing(fn($state) => $state->label())
                            ->label('Status'),
                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->visible(fn($record): bool => $record->isRejected() && $record->rejection_reason)
                            ->label('Rejection reason'),
                        Infolists\Components\TextEntry::make('verified_at')
                            ->dateTime()
                            ->label('Verification date'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->label('Registration date'),
                    ])->columns(2),

                Infolists\Components\Section::make('Vendor products')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('products')
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label('Title'),
                                Infolists\Components\TextEntry::make('price')
                                    ->money('USD')
                                    ->label('Price'),
                                Infolists\Components\TextEntry::make('status')
                                    ->badge()
                                    ->label('Status'),
                            ])
                            ->columns(3),
                    ])
                    ->visible(fn($record): bool => $record->products()->count() > 0),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::pending()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::pending()->count() > 0 ? 'warning' : null;
    }
}
