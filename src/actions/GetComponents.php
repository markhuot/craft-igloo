<?php

namespace markhuot\igloo\actions;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\db\Query;
use markhuot\igloo\base\Collection;
use markhuot\igloo\db\Table;

class GetComponents
{
    static $eagerLoadMap = [];

    function getRows(ElementInterface $element, ?FieldInterface $field)
    {
        return collect((new Query)
            ->select(['paths.*', 'components.uid'])
            ->from(Table::COMPONENTS_PATHS . ' paths')
            ->where([
                'ancestor' => $element->id,
                'fieldId' => $field->id,
            ])
            ->leftJoin(Table::COMPONENTS . ' components', '[[components.parentId]]=[[paths.ancestor]] and [[components.childId]]=[[paths.descendant]]')
            ->orderBy('paths.depth asc, components.sort asc')
            ->all());
    }

    function getComponents(ElementInterface $element, ?FieldInterface $field)
    {
        $paths = $this->getRows($element, $field);

        $newPaths = $paths
            ->filter(function ($path) {
                return collect(static::$eagerLoadMap)
                        ->where('id', '=', $path['descendant'])
                        ->count() == 0;
            })
            ->mapWithKeys(function ($path) {
                $element = \Craft::$app->elements->getElementById($path['descendant']);

                return [$element->id => $element];
            });

        static::$eagerLoadMap = static::$eagerLoadMap + $newPaths->toArray();

        return (new Collection($paths, $field))
            ->filter(fn ($p) => (int)$p['depth'] === 1)
            ->map(fn ($p) => static::$eagerLoadMap[$p['descendant']])
            ->values();
    }
}
