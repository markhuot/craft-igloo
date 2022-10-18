<?php

namespace markhuot\igloo\actions;

use craft\db\Query;
use markhuot\igloo\db\Table;
use yii\db\conditions\OrCondition;
use craft\base\ElementInterface;

class RemapDraftComponents
{
    function needsRemapping(ElementInterface $element, array $uids)
    {
        $elementId = (new Query)
            ->select('parentId')
            ->from(Table::COMPONENTS)
            ->where(['uid' => $uids])
            ->one();

        return $elementId !== $element->id;
    }

    function handle(ElementInterface $element, array $uids)
    {
        $oldRows = (new Query)
            ->from(Table::COMPONENTS)
            ->where(['uid' => $uids])
            ->all();

        return (new Query)
            ->select('uid')
            ->from(Table::COMPONENTS)
            ->where(new OrCondition(array_map(fn ($oldRow) => [
                'parentId' => $element->id,
                'fieldId' => $oldRow['fieldId'],
                'slot' => $oldRow['slot'],
                'childId' => $oldRow['childId'],
                'sort' => $oldRow['sort'],
            ], $oldRows)))
            ->column();
    }
}
