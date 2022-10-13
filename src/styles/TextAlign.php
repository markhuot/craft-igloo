<?php

namespace markhuot\igloo\styles;

class TextAlign extends Style
{
    static function definition()
    {
        return [
            'textAlign' => [
                'label' => 'Text Align',
                'attribute' => 'text-align',
                'template' => 'igloo/components/text-field'
            ],
        ];
    }
}
