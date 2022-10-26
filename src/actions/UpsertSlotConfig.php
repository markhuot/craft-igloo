<?php

namespace markhuot\igloo\actions;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\db\Query;
use markhuot\igloo\db\Table;

class UpsertSlotConfig
{
    function handle(FieldInterface $field, ElementInterface|null $element, int|null $columns, string|null $template)
    {
        \Craft::$app->db->createCommand()->upsert(Table::CONFIG, [
            'fieldId' => $field->id,
            'elementId' => $element->id,
            'columns' => $columns,
            'template' => $template,
        ], [
            'columns' => $columns,
            'template' => $template,
        ])->execute();
    }
}
