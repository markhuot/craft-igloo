<?php

namespace markhuot\igloo\styles;

class Position extends Style
{
    static function definition()
    {
        return [
            'position' => [
                'label' => 'Position',
                'attribute' => 'position',
                'template' => 'igloo/components/select-field',
                'options' => [
                    'absolute' => 'Absolute',
                    'fixed' => 'Fixed',
                ]
            ],
            'top',
            'left',
        ];
    }
}
