<?php

namespace markhuot\igloo\services;

use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\db\Query;
use markhuot\igloo\db\Table;

class Tree
{

    function attach(ElementInterface $element, FieldInterface $field, array $componentIds, $index=0)
    {
        foreach ($componentIds as $componentId) {
            $data = [
                'elementId' => $element->id,
                'slot' => $field->handle,
                'componentId' => $componentId,
                'lft' => 0,
                'rgt' => 1,
            ];

            \Craft::$app->db->createCommand()->insert(Table::COMPONENTS, $data)->execute();
        }
    }

    function get(ElementInterface $element, string $slot)
    {
        $records = (new Query())
            ->from(Table::COMPONENTS)
            ->where([
                'elementId' => $element->id,
                'slot' => $slot,
            ])
            ->orderBy('lft')
            ->all();

        return array_map(function ($record) {
            return \Craft::$app->elements->getElementById($record['componentId']);
        }, $records);
    }

    function detach(ElementInterface $element, Field $field, array $componentIds)
    {
        foreach ($componentIds as $componentId) {
            \Craft::$app->db->createCommand()->delete(Table::COMPONENTS, [
                'elementId' => $element->id,
                'slot' => $field->handle,
                'componentId' => $componentId,
            ])->execute();
        }
    }

}
