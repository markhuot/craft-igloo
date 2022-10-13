<?php

namespace markhuot\igloo\actions;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use markhuot\igloo\db\Table;

class UpsertStyles
{
    function handle(ElementInterface $element, ?FieldInterface $field, array $styles)
    {
        $transaction = \Craft::$app->db->beginTransaction();

        $existingId = (new Query)
            ->select('id')
            ->from(Table::STYLES)
            ->where([
                'elementId' => $element->id,
                'fieldId' => $field?->id,
            ])
            ->scalar();

        if (empty($existingId)) {
            \Craft::$app->db->createCommand()->insert(Table::STYLES, [
                'elementId' => $element->id,
                'fieldId' => $field?->id,
                'styles' => json_encode($styles),
                'dateCreated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
                'dateUpdated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
                'uid' => StringHelper::UUID(),
            ])->execute();
        }
        else {
            \Craft::$app->db->createCommand()->update(Table::STYLES, [
                'styles' => json_encode($styles),
                'dateUpdated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
            ], [
                'elementId' => $element->id,
                'fieldId' => $field?->id,
            ])->execute();
        }

        $transaction->commit();
    }
}
