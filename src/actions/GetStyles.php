<?php

namespace markhuot\igloo\actions;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\db\Query;
use markhuot\igloo\db\Table;

class GetStyles
{
    function handle(ElementInterface $element, ?FieldInterface $field)
    {
        $styleData = json_decode((new Query)
            ->select('styles')
            ->from(Table::STYLES)
            ->where([
                'elementId' => $element->id,
                'fieldId' => $field?->id,
            ])
            ->scalar() ?? '[]', true);

        $styles = collect([
            'fontFamily' => new \markhuot\igloo\styles\FontFamily($styleData),
            'fontSize' => new \markhuot\igloo\styles\FontSize($styleData),
            'fontStyle' => new \markhuot\igloo\styles\FontStyle($styleData),
            'fontWeight' => new \markhuot\igloo\styles\FontWeight($styleData),
            'fontColor' => new \markhuot\igloo\styles\FontColor($styleData),
            'letterSpacing' => new \markhuot\igloo\styles\LetterSpacing($styleData),
            'lineHeight' => new \markhuot\igloo\styles\LineHeight($styleData),
            'textAlign' => new \markhuot\igloo\styles\TextAlign($styleData),
            'position' => new \markhuot\igloo\styles\Position($styleData),
            'sizing' => new \markhuot\igloo\styles\Sizing($styleData),
        ]);

        return $styles;
    }
}
