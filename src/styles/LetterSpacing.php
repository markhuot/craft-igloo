<?php

namespace markhuot\igloo\styles;

use Illuminate\Support\Collection;

class LetterSpacing extends Style
{
    static function definition()
    {
        return [
            'letterSpacing' => [
                'label' => 'Letter Spacing',
                'attribute' => 'letter-spacing',
                'template' => 'igloo/components/text-field'
            ],
        ];
    }
}
