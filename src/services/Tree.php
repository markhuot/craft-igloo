<?php

namespace markhuot\igloo\services;

use craft\base\ElementInterface;
use craft\base\Field;
use craft\db\Query;
use markhuot\igloo\db\Table;

class Tree
{

    function attach(ElementInterface $element, Field $field, array $componentIds, $index=0)
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

    function get(ElementInterface $element, Field $field)
    {
        $records = (new Query())
            ->from(Table::COMPONENTS)
            ->where([
                'elementId' => $element->id,
                'slot' => $field->handle,
            ])
            ->orderBy('lft')
            ->all();

        return array_map(function ($record) {
            return \Craft::$app->elements->getElementById($record['componentId']);
        }, $records);
    }

}
