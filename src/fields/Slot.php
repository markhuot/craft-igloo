<?php

namespace markhuot\igloo\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use markhuot\igloo\actions\GetComponents;
use markhuot\igloo\actions\GetSlotConfig;
use markhuot\igloo\Igloo;

class Slot extends Field
{
    public static function hasContentColumn(): bool
    {
        return false;
    }

    function getInputHtml($value, ElementInterface $element = null): string
    {
        $isRootSlot = false;
        $elementId = \Craft::$app->requestedParams['elementId'] ?? null;
        if ($elementId) {
            $routeElement = \Craft::$app->elements->getElementById($elementId);
            if (in_array($routeElement->id, [$element->id, $element->getCanonicalId()])) {
                $isRootSlot = true;
            }
        }

        return Craft::$app->getView()->renderTemplate('igloo/fields/slot', [
            'field' => $this,
            'element' => $element,
            'isRootSlot' => $isRootSlot,
            'config' => (new GetSlotConfig)->handle($this, $element),
        ]);
    }

    function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if (empty($element->id)) {
            return null;
        }

        return (new GetComponents)->getComponents($element, $this);
    }

    function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if (!$element) {
            return [];
        }

        return (new GetComponents)
            ->getRows($element, $this)
            ->where('depth', '=', 1)
            ->pluck('descendant')
            ->toArray();
    }

    function afterElementPropagate(ElementInterface $element, bool $isNew): void
    {
        $action = null;
        if ($element?->getIsDraft() && $isNew && $element->duplicateOf) {
            $action = 'duplicate';
        }
        else if (!$element?->getIsDraft() && $element?->duplicateOf?->getIsDraft()) {
            $action = 'copy';
        }
        else {
            return;
        }

        $oldComponentIds = (new GetComponents)
            ->getRows($element, $this)
            ->where('depth', '=', 1);

        $newComponentIds = (new GetComponents)
            ->getRows($element->duplicateOf, $this)
            ->where('depth', '=', 1);

        if ($action === 'copy') {
            Igloo::getInstance()->tree->detach($element, $this, 'default', $oldComponentIds->pluck('uid')->toArray());
            Igloo::getInstance()->tree->attach($element, $this, 'default', $newComponentIds->pluck('descendant')->toArray(), null, 'beforeend');
        }
        else if ($action === 'duplicate') {
            Igloo::getInstance()->tree->attach($element, $this, 'default', $newComponentIds->pluck('descendant')->toArray(), null, 'beforeend');
        }
    }

}
