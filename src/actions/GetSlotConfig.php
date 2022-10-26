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

        if ($row['columnSizes'] ?? false) {
            $row['columnSizes'] = json_decode($row['columnSizes'], true);
        }

        if ($columnCount = ($row['columns'] ?? false)) {
            $row['columnWidths'] = [];
            for ($i=0; $i<$columnCount-1; $i++) {
                $lastStop = $row['columnWidths'][$i-1] ?? 0;
                $desiredStop = $row['columnSizes'][$i] ?? (($i+1) / $columnCount);
                $row['columnSizes'][$i] = $desiredStop;
                $row['columnWidths'][$i] = $desiredStop - $lastStop;
            }
            $row['columnWidths'][$columnCount-1] = 1 - $row['columnSizes'][$i-1];
        }

        return $row;
    }
}
