<?php

namespace markhuot\igloo\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
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
        return Craft::$app->getView()->renderTemplate('igloo/fields/slot', [
            'field' => $this,
            'element' => $element,
            'config' => (new GetSlotConfig)->handle($this, $element),
        ]);
    }

}
