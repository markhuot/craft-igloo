<?php

namespace markhuot\igloo\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\db\Query;
use markhuot\igloo\actions\GetSlotConfig;
use markhuot\igloo\db\Table;
use markhuot\igloo\Igloo;

class Slot extends Field
{
    static $eagerLoadMap = [];

    public static function hasContentColumn(): bool
    {
        return false;
    }

    function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('igloo/fields/slot', [
            'field' => $this,
            'element' => $element,
            'config' => (new GetSlotConfig)->handle($this, $element),
        ]);
    }

    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if (empty($element->id)) {
            return null;
        }

        $paths = (new Query)
            ->select('paths.*')
            ->from(Table::COMPONENTS_PATHS . ' paths')
            ->where([
                'ancestor' => $element->id,
                //'fieldId' => $this->id,
            ])
            ->leftJoin(Table::COMPONENTS . ' components', '[[components.parentId]]=[[paths.ancestor]] and [[components.childId]]=[[paths.descendant]]')
            ->orderBy('paths.depth asc, components.sort asc')
            ->all();

        $newPaths = collect($paths)
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
        
        return collect($paths)
            ->map(fn ($p) => static::$eagerLoadMap[$p['descendant']])
            ->values()
            ->toArray();

    }

}
