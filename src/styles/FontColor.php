<?php

namespace markhuot\igloo\styles;

use Illuminate\Support\Collection;

class FontColor extends Style
{
    static function definition()
    {
        return [
            'fontColor' => [
                'label' => 'Font Color',
                'attribute' => 'color',
                'template' => 'igloo/components/color-field'
            ],
        ];
    }
}
