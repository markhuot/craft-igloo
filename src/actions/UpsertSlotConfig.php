<?php

namespace markhuot\igloo\actions;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\db\Query;
use markhuot\igloo\db\Table;

class UpsertSlotConfig
{
    function handle(
        FieldInterface $field,
        ElementInterface|null $element,
        array $config,
    )
    {
        if ($config['columnSizes'] ?? false) {
            $config['columnSizes'] = json_encode($config['columnSizes']);
        }

        \Craft::$app->db->createCommand()->upsert(Table::CONFIG, array_merge($config, [
            'fieldId' => $field->id,
            'elementId' => $element->id,
        ]), $config)->execute();
    }
}
