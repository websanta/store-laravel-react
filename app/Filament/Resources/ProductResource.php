<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Enums\ProductStatusEnum;
use Filament\Resources\Resource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\Pages\EditProduct;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-s-shopping-bag';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->live(onBlur: true)
                    ->required()
                    ->afterStateUpdated(
                        function (string $operation, $state, callable $set) {
                            $set("slug", Str::slug($state));
                        }
                    ),
                TextInput::make('slug')
                    ->required(),
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Department')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(
                        function (callable $set) {
                            $set('category_id', null);
                        }
                    ),
                Select::make('category_id')
                    ->relationship(
                        'category',
                        'name',
                        modifyQueryUsing: function (Builder $query, callable $get) {
                            $departmentId = $get('department_id');
                            if ($departmentId) {
                                $query->where('department_id', $departmentId);
                            }
                        }
                    )
                    ->label('Category')
                    ->preload()
                    ->searchable()
                    ->required(),
                RichEditor::make('description')
                    ->required()
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'italic',
                        'underline',
                        'bulletList',
                        'orderedList',
                        'h2',
                        'h3',
                        'link',
                        'undo',
                        'redo',
                        'strike',
                        'table',
                    ])
                    ->columnSpan(2),
                TextInput::make('price')
                    ->numeric()
                    ->required(),
                TextInput::make('quantity')
                    ->integer(),
                Select::make('status')
                    ->options(ProductStatusEnum::labels())
                    ->default(ProductStatusEnum::Draft->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->collection('images')
                    ->limit(1)
                    ->label('Image')
                    ->conversion('thumb'),
                TextColumn::make('title')
                    ->sortable()
                    ->words(10)
                    ->searchable(),
                TextColumn::make('price')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('status')
                    ->badge()
                    ->colors(ProductStatusEnum::colors()),
                TextColumn::make('department.name'),
                TextColumn::make('category.name'),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ProductStatusEnum::labels()),
                SelectFilter::make('department_id')
                    ->relationship('department', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'images' => Pages\ProductImages::route('/{record}/images'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditProduct::class,
            Pages\ProductImages::class,
        ]);
    }
}
