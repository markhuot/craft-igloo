<?php

namespace markhuot\igloo\styles;

class LineHeight extends Style
{
    static function definition()
    {
        return [
            'lineHeight' => [
                'label' => 'Line Height',
                'attribute' => 'line-height',
                'template' => 'igloo/components/text-field'
            ],
        ];
    }
}
