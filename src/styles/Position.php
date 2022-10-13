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
            'top' => [
                'label' => 'Top',
                'attribute' => 'top',
                'template' => 'igloo/components/text-field'
            ],
            'left' => [
                'label' => 'Left',
                'attribute' => 'left',
                'template' => 'igloo/components/text-field'
            ],
        ];
    }
}
