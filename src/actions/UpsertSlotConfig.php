<?php

namespace markhuot\igloo\actions;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\db\Query;
use markhuot\igloo\db\Table;

class UpsertSlotConfig
{
    function handle(FieldInterface $field, ElementInterface|null $element, int|null $columns)
    {
        \Craft::$app->db->createCommand()->upsert(Table::CONFIG, [
            'fieldId' => $field->id,
            'elementId' => $element->id,
            'columns' => $columns,
        ], [
            'columns' => $columns,
        ])->execute();
    }
}
