<?php

namespace markhuot\igloo\actions;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\db\Query;
use markhuot\igloo\db\Table;

class GetSlotConfig
{
    function handle(FieldInterface $field, ElementInterface|null $element)
    {
        return (new Query)->from(Table::CONFIG)->where([
            'fieldId' => $field->id,
            'elementId' => $element?->id,
        ])->one();
    }
}
