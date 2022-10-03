<?php

namespace markhuot\igloo;

use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Fields;
use craft\web\UrlManager;
use craft\web\View;
use markhuot\igloo\services\Tree;
use markhuot\igloo\twig\Extension;
use yii\base\Event;

/**
 * @property Tree $tree
 */
class Igloo extends Plugin
{

        function init()
        {
            $this->components = [
                'tree' => Tree::class,
            ];

            Event::on(
                Fields::class,
                Fields::EVENT_REGISTER_FIELD_TYPES,
                function (RegisterComponentTypesEvent $event) {
                    $event->types[] = \markhuot\igloo\fields\Slot::class;
                }
            );

            Event::on(
                UrlManager::class,
                UrlManager::EVENT_REGISTER_CP_URL_RULES,
                function (RegisterUrlRulesEvent $event) {
                    $event->rules['igloo/content'] = 'igloo/content/index';
                }
            );

            \Craft::$app->view->registerTwigExtension(new Extension);

            // Event::on(
            //     View::class,
            //     View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            //     function (RegisterTemplateRootsEvent $event) {
            //         $event->roots['igloo'] = __DIR__ . '/templates';
            //     }
            // );
        }

}
