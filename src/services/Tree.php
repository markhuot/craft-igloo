<?php

namespace markhuot\igloo\services;

use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\db\Query;
use craft\helpers\StringHelper;
use markhuot\igloo\data\SlotData;
use markhuot\igloo\db\Table;
use yii\db\Expression;

class Tree
{

    function attach(ElementInterface $element, string $slot, array $componentIds, string|null $scope, string $position)
    {
        if ($scope) {
            $scope = (new Query)
                ->from(Table::COMPONENTS)
                ->where(['uid' => $scope])
                ->one();
        }

        if (empty($scope) && $position === 'beforeend') {
            $max = (new Query)
                ->select('MAX(rgt)')
                ->from(Table::COMPONENTS)
                ->where([
                    'elementId' => $element->id,
                    'slot' => $slot,
                ])
                ->scalar() ?? 0;
        }
        else if ($scope && $position === 'beforebegin') {
            $max = $scope['lft'] - 1;
        }
        else if ($scope && $position === 'afterbegin') {
            // not necessary for our app
            throw new \Exception('Not implemented');
        }
        else if ($scope && $position === 'beforeend') {
            $max = $scope['lft'];
        }
        else if ($scope && $position === 'afterend') {
            $max = $scope['rgt'];
        }

        \Craft::$app->db->createCommand()->update(Table::COMPONENTS, [
            'lft' => new \yii\db\Expression('`lft`+'.(count($componentIds) * 2)),
            'rgt' => new \yii\db\Expression('`rgt`+'.(count($componentIds) * 2)),
        ], ['and',
            ['elementId' => $element->id],
            ['slot' => $slot],
            ['>', 'lft', (int)$max]
        ])->execute();

        if (!empty($scope) && $position === 'beforeend') {
            \Craft::$app->db->createCommand()->update(Table::COMPONENTS, [
                'rgt' => new \yii\db\Expression('`rgt`+'.(count($componentIds) * 2)),
            ], ['and',
                ['>=', 'rgt', (int)$scope['rgt']],
                ['<=', 'lft', (int)$scope['lft']],
            ])->execute();
        }

        $ids = [];
        foreach ($componentIds as $index => $componentId) {
            $data = [
                'elementId' => $element->id,
                'slot' => $slot,
                'componentId' => $componentId,
                'lft' => $max + ($index * 2) + 1,
                'rgt' => $max + ($index * 2) + 2,
                'uid' => StringHelper::UUID(),
            ];

            \Craft::$app->db->createCommand()->insert(Table::COMPONENTS, $data)->execute();
            $ids[] = \Craft::$app->db->lastInsertID;
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

        return $data;
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

        if (empty($records)) {
            $records = (new Query())
                ->select('children.*')
                ->from(Table::COMPONENTS . ' component')
                ->where([
                    'component.componentId' => $element->id,
                ])
                ->innerJoin(Table::COMPONENTS . ' children', '[[children.elementId]]=[[component.elementId]] and [[children.slot]]=[[component.slot]] and [[children.lft]]>[[component.lft]] and [[children.rgt]]<[[component.rgt]]')
                ->orderBy('lft')
                ->all();
        }

        $elements = [];
        $skipUntil = null;
        foreach ($records as $record) {
            if ($skipUntil && $record['lft'] < $skipUntil) {
                continue;
            }

            $element = \Craft::$app->elements->getElementById($record['componentId']);
            $element->setSlot($record);
            $skipUntil = $record['rgt'];

            $elements[] = $element;
        }

        return $elements;
    }


    function detach(ElementInterface $element, string $slot, array $componentUids)
    {
        foreach ($componentUids as $componentUid) {
            $row = (new Query)
                ->from(Table::COMPONENTS)
                ->where(['uid' => $componentUid])
                ->one();

            \Craft::$app->db->createCommand()->delete(Table::COMPONENTS, ['and',
                ['elementId' => $row['elementId']],
                ['slot' => $row['slot']],
                ['>=', 'lft', $row['lft']],
                ['<=', 'rgt', $row['rgt']],
            ])->execute();

            \Craft::$app->db->createCommand()->update(Table::COMPONENTS, [
                'rgt' => new \yii\db\Expression('`rgt`-'.($row['rgt']-$row['lft']+1)),
            ], ['and',
                ['>=', 'rgt', (int)$row['rgt']],
                ['<=', 'lft', (int)$row['lft']],
            ])->execute();

            \Craft::$app->db->createCommand()->update(Table::COMPONENTS, [
                'lft' => new \yii\db\Expression('`lft`-'.($row['rgt']-$row['lft']+1)),
                'rgt' => new \yii\db\Expression('`rgt`-'.($row['rgt']-$row['lft']+1)),
            ], ['>', 'lft', (int)$row['lft']])->execute();
        }
    }

}
