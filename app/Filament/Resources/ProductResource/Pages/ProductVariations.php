<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use App\Enums\ProductVariationTypeEnum;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProductResource;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class ProductVariations extends EditRecord
{
    protected static string $resource = ProductResource::class;
    protected static ?string $title = 'Variations';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public function form(Form $form): Form
    {
        $types = $this->record->variationTypes;
        $fields = [];

        foreach ($types as $type) {
            $fields[] = Hidden::make('variation_type_' . ($type->id) . '.id')
                ->default(fn($get, $state) => $state);

            $fields[] = TextInput::make('variation_type_' . ($type->id) . '.name')
                ->label($type->name)
                ->disabled()
                ->dehydrated(false);
        }

        return $form
            ->schema([
                Repeater::make('variations')
                    ->collapsible()
                    ->label(false)
                    ->addable(false)
                    ->defaultItems(1)
                    ->schema([
                        Hidden::make('id'),
                        Section::make()
                            ->schema($fields)
                            ->columns(3),
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric(),
                        TextInput::make('price')
                            ->label('Price')
                            ->numeric(),
                    ])
                    ->columns(2)
                    ->columnSpan(2)
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $variations = $this->record->variations->toArray();
        $data['variations'] = $this->mergeCartesianWithExisting($this->record->variationTypes, $variations);
        return $data;
    }

    private function mergeCartesianWithExisting($variationTypes, $existingData)
    {
        $defaultQuantity = $this->record->quantity;
        $defaultPrice = $this->record->price;
        $cartesianProduct = $this->cartesianProduct($variationTypes, $defaultQuantity, $defaultPrice);
        $mergedResult = [];

        foreach ($cartesianProduct as $product) {
            $optionIds = collect($product)
                ->filter(fn($value, $key) => str_starts_with($key, 'variation_type_'))
                ->map(fn($option) => $option['id'])
                ->values()
                ->toArray();

            $match = array_filter($existingData, function ($existingOption) use ($optionIds) {
                return $existingOption['variation_type_option_ids'] === $optionIds;
            });

            if (!empty($match)) {
                $existingEntry = reset($match);
                $product['id'] = $existingEntry['id'];
                $product['quantity'] = $existingEntry['quantity'];
                $product['price'] = $existingEntry['price'];
            } else {
                // Do not set id for new variations.
                $product['quantity'] = $defaultQuantity;
                $product['price'] = $defaultPrice;
            }

            $mergedResult[] = $product;
        }

        return $mergedResult;
    }

    private function cartesianProduct($variationTypes, $defaultQuantity = null, $defaultPrice = null)
    {
        $result = [[]];

        foreach ($variationTypes as $index => $variationType) {
            $temp = [];

            foreach ($variationType->options as $option) {
                // Add current option to all existing
                foreach ($result as $combination) {
                    $newCombination = $combination + [
                        'variation_type_' . ($variationType->id) => [
                            'id' => $option->id,
                            'name' => $option->name,
                            'type' => $variationType->name,
                        ],
                    ];

                    $temp[] = $newCombination;
                }
            }

            $result = $temp;
        }

        // Add quantity and price to completed combinations
        foreach ($result as &$combination) {
            if (count($combination) === count($variationTypes)) {
                $combination['quantity'] = $defaultQuantity;
                $combination['price'] = $defaultPrice;
            }
        }

        return $result;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $formattedData = [];

        foreach ($data['variations'] as $option) {
            $variationTypeOptionIds = [];

            foreach ($this->record->variationTypes as $variationType) {
                $key = 'variation_type_' . $variationType->id;

                // Сhecking the existence of the key and get the ID
                if (isset($option[$key])) {
                    if (is_array($option[$key]) && isset($option[$key]['id'])) {
                        $variationTypeOptionIds[] = (int)$option[$key]['id'];
                    } elseif (is_numeric($option[$key])) {
                        // If it is already an ID (number or string)
                        $variationTypeOptionIds[] = (int)$option[$key];
                    }
                }
            }

            $variationData = [
                'variation_type_option_ids' => $variationTypeOptionIds,
                'quantity' => $option['quantity'] ?? null,
                'price' => $option['price'] ?? null,
            ];

            // Add id only if it exists
            if (isset($option['id']) && !empty($option['id'])) {
                $variationData['id'] = $option['id'];
            }

            $formattedData[] = $variationData;
        }

        $data['variations'] = $formattedData;
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $variations = $data['variations'];
        unset($data['variations']);

        foreach ($variations as $variation) {
            if (isset($variation['id']) && !empty($variation['id'])) {
                // Updating an existing variation
                $record->variations()->updateOrCreate(
                    ['id' => $variation['id']],
                    [
                        'variation_type_option_ids' => $variation['variation_type_option_ids'],
                        'quantity' => $variation['quantity'],
                        'price' => $variation['price'],
                    ]
                );
            } else {
                // Create a new one if such a combination does not exist yet.
                $record->variations()->updateOrCreate(
                    ['variation_type_option_ids' => $variation['variation_type_option_ids']],
                    [
                        'quantity' => $variation['quantity'],
                        'price' => $variation['price'],
                    ]
                );
            }
        }

        return $record;
    }
}
