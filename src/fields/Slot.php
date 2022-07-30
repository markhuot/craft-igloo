<?php

namespace markhuot\igloo\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use markhuot\igloo\Igloo;

class Slot extends Field
{

    function getInputHtml(mixed $value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('igloo/fields/slot', [
            'field' => $this,
            'element' => $element,
            'tree' => Igloo::getInstance()->tree->get($element, $this),
        ]);
    }

}
