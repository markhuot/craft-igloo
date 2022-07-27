<?php

namespace markhuot\igloo;

use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use yii\base\Event;

class Igloo extends Plugin
{

        function init()
        {
            Event::on(
                Fields::class,
                Fields::EVENT_REGISTER_FIELD_TYPES,
                function (RegisterComponentTypesEvent $event) {
                    $event->types[] = \markhuot\igloo\fields\Igloo::class;
                }
            );
        }

}
