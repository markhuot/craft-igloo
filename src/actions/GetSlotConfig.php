<?php

namespace markhuot\igloo\actions;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\db\Query;
use markhuot\igloo\db\Table;

class GetSlotConfig
{
    function handle(FieldInterface $field, ElementInterface|null $element)
    {
        $row = (new Query)->from(Table::CONFIG)->where([
            'fieldId' => $field->id,
            'elementId' => $element?->id,
        ])->one();

        if ($row['template'] ?? false) {
            $row['template'] = json_decode($row['template'], true);
        }

        if ($columnCount = ($row['columns'] ?? false)) {
            $row['columnWidths'] = [];
            $lastStop = 0;
            for ($i=0; $i<$columnCount-1; $i++) {
                $desiredStop = $row['template']['dividers'][$i] ?? false;
                if (!$desiredStop && $i < $columnCount - 1) {
                    $desiredStop = ($i+1) / $columnCount;
                }
                $lastStop = $row['columnWidths'][$i] = $desiredStop;
            }
            $row['columnWidths'][$columnCount-1] = 1 - $lastStop;
        }

        return $row;
    }
}
