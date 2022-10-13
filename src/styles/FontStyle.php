<?php

namespace markhuot\igloo\styles;

use Illuminate\Support\Collection;

class FontStyle extends Style
{
    static function definition()
    {
        return [
            'fontStyle' => [
                'label' => 'Font Style',
                'attribute' => 'font-style',
                'template' => 'igloo/components/text-field'
            ],
        ];
    }
}
