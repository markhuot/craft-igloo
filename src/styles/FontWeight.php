<?php

namespace markhuot\igloo\styles;

use Illuminate\Support\Collection;

class FontWeight extends Style
{
    static function definition()
    {
        return [
            'fontWeight' => [
                'label' => 'Font Weight',
                'attribute' => 'font-weight',
                'template' => 'igloo/components/text-field'
            ],
        ];
    }
}
