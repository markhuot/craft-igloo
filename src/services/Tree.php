<?php

namespace markhuot\igloo\services;

use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use markhuot\igloo\data\SlotData;
use markhuot\igloo\db\Table;
use yii\db\Expression;

class Tree
{

    function attach(ElementInterface $element, FieldInterface $field, string $slot, array $componentIds, string|null $scope, string $position)
    {
        if ($scope) {
            $scope = (new Query)
                ->from(Table::COMPONENTS)
                ->where(['uid' => $scope])
                ->one();
        }

        if ($scope && $position === 'beforebegin') {
            $sort = $scope['sort'];
        }
        else if ($scope && $position === 'afterend') {
            $sort = $scope['sort'] + 1;
        }
        else {
            $sort = 0;
        }

        \Craft::$app->db->createCommand()->update(Table::COMPONENTS, [
            'sort' => new Expression('`sort`+' . count($componentIds)),
        ], ['and',
            ['parentId' => $element->id],
            ['fieldId' => $field->id],
            ['slot' => $slot],
            ['>=', 'sort', $sort],
        ])->execute();

        $ids = [];
        foreach ($componentIds as $index => $componentId) {
            $data = [
                'parentId' => $element->id,
                'fieldId' => $field->id,
                'slot' => $slot,
                'childId' => $componentId,
                'sort' => $sort + $index,
                'dateCreated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
                'uid' => StringHelper::UUID(),
            ];

            \Craft::$app->db->createCommand()->insert(Table::COMPONENTS, $data)->execute();
            $ids[] = \Craft::$app->db->lastInsertID;

            $rows = [];

            $ancestors = (new Query)
                ->from(Table::COMPONENTS_PATHS)
                ->where(['descendant' => $element->id])
                ->all();
            $rows = array_merge($rows, array_map(fn ($row) => [
                'ancestor' => $row['ancestor'],
                'descendant' => $componentId,
                'depth' => $row['depth'] + 1,
                'dateCreated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
                'uid' => StringHelper::UUID(),
            ], $ancestors));

            $descendants = (new Query)
                ->from(Table::COMPONENTS_PATHS)
                ->where(['ancestor' => $componentId])
                ->all();
            $rows = array_merge($rows, array_map(fn ($row) => [
                'ancestor' => $element->id,
                'descendant' => $row['descendant'],
                'depth' => $row['depth'] + 1,
                'dateCreated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
                'uid' => StringHelper::UUID(),
            ], $descendants));

            $rows[] = [
                'ancestor' => $element->id,
                'descendant' => $componentId,
                'depth' => 1,
                'dateCreated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
                'uid' => StringHelper::UUID(),
            ];
            
            // $rows[] = [
            //     'ancestor' => $componentId,
            //     'descendant' => $componentId,
            //     'depth' => 0,
            //     'dateCreated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
            //     'uid' => StringHelper::UUID(),
            // ];
            
            foreach ($rows as $row) {
                //var_dump($row);
                \Craft::$app->db->createCommand()->insert(Table::COMPONENTS_PATHS, $row)->execute();
            }
        }

        $records = (new Query)
            ->from(Table::COMPONENTS)
            ->where(['id' => $ids])
            ->all();

        /** @var SlotData[] $data */
        $data = [];
        foreach ($records as $record) {
            $data[] = new SlotData($record);
        }

        // reset join data. we need to do this so subsequent requests
        // will pull join data out of the DB again and get this newly
        // attached data
        $element->{$field->handle} = null;

        return $data;
    }

    function get(ElementInterface $element, FieldInterface $field, string $slot='default')
    {
        $records = (new Query())
            ->from(Table::COMPONENTS)
            ->where([
                'parentId' => $element->id,
                'fieldId' => $field->id,
                'slot' => $slot,
            ])
            ->orderBy('sort')
            ->all();

        /** @var ElementInterface[] $elements */
        $elements = [];
        foreach ($records as $record) {
            $element = \Craft::$app->elements->getElementById($record['childId']);
            $element->setSlot($record);

            $elements[] = $element;
        }

        return $elements;
    }


    function detach(ElementInterface $element, FieldInterface $field, string $slot, array $componentUids)
    {
        foreach ($componentUids as $componentUid) {
            $row = (new Query)
                ->from(Table::COMPONENTS)
                ->where(['uid' => $componentUid])
                ->one();

            \Craft::$app->db->createCommand()->update(Table::COMPONENTS, [
                'sort' => new Expression('`sort`-1'),
            ], ['and',
                ['parentId' => $element->id],
                ['fieldId' => $field->id],
                ['slot' => $slot],
                ['>', 'sort', $row['sort']],
            ])->execute();

            \Craft::$app->db->createCommand()->delete(Table::COMPONENTS, [
                'uid' => $componentUid
            ])->execute();
        }
    }

}
