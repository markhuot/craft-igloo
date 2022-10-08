<?php

namespace markhuot\igloo\actions;

use craft\base\ElementInterface;
use craft\db\Query;
use markhuot\igloo\data\SlotData;
use markhuot\igloo\db\Table;

class GetLeavesForComponent
{
    function handle(ElementInterface $element)
    {
        $records = (new Query)
            ->from(Table::COMPONENTS)
            ->where(['componentId' => $element->id])
            ->all();

        /** @var SlotData[] $data */
        $data = [];
        foreach ($records as $record) {
            $data[] = new SlotData($record);
        }

        return $data;
    }
}
