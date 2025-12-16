<?php

namespace App\Enums;

enum ProductVariationTypeEnum: string
{
    case Select = 'Select';
    case Radio = 'Radio';
    case Image = 'Image';

    public static function labels(): array
    {
        return [
            self::Select->value => 'Select',
            self::Radio->value => 'Radio',
            self::Image->value => 'Image',
        ];
    }
}
