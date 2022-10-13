<?php

namespace markhuot\igloo\styles;

use Illuminate\Support\Collection;

class FontFamily extends Style
{
    static function definition()
    {
        return [
            'fontFamily' => [
                'label' => 'Font Family',
                'attribute' => 'font-family',
                'template' => 'igloo/components/select-field'
            ],
        ];
    }

    protected function getInputVars(string $name)
    {
        return ['options' => [
            'helvetica' => 'Helvetica',
            'arial' => 'Arial',
        ]];
    }
}
