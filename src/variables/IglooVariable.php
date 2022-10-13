<?php

namespace markhuot\igloo\variables;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\db\Query;
use markhuot\igloo\actions\GetSlotConfig;
use markhuot\igloo\data\StyleData;
use markhuot\igloo\db\Table;
use markhuot\igloo\Igloo;

class IglooVariable
{
    function fieldConfig(FieldInterface $field, ElementInterface|null $element)
    {
        return (new GetSlotConfig)->handle($field, $element);
    }

    function styles(ElementInterface $element, ?FieldInterface $field)
    {
        $styles = (new Query)
            ->select('styles')
            ->from(Table::STYLES)
            ->where([
                'elementId' => $element->id,
                'fieldId' => $field?->id,
            ])
            ->scalar();

        return new StyleData(json_decode($styles, true));
    }

    function components(ElementInterface $element, FieldInterface $field, string $slot)
    {
        return Igloo::getInstance()->tree->get($element, $field, $slot);
    }
}
