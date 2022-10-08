<?php

namespace markhuot\igloo\variables;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use markhuot\igloo\actions\GetSlotConfig;
use markhuot\igloo\Igloo;

class IglooVariable
{
    function fieldConfig(FieldInterface $field, ElementInterface|null $element)
    {
        return (new GetSlotConfig)->handle($field, $element);
    }

    function components(ElementInterface $element, FieldInterface $field, string $slot)
    {
        return Igloo::getInstance()->tree->get($element, $field, $slot);
    }
}
