<?php

namespace markhuot\igloo\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;

class Igloo extends Field
{

    function getInputHtml(mixed $value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('igloo/fields/igloo', [
            'foo' => 'bar',
        ]);
    }

}
