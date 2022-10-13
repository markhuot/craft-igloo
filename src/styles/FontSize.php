<?php

namespace markhuot\igloo\styles;

use Illuminate\Support\Collection;

class FontSize extends Style
{
    static function definition()
    {
        return [
            'fontSize' => [
                'label' => 'Font Size',
                'attribute' => 'font-size',
                'template' => 'igloo/components/text-field'
            ],
        ];
    }
}
