<?php

namespace markhuot\igloo;

use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use markhuot\igloo\behaviors\SlottedElementBehavior;
use markhuot\igloo\services\Tree;
use markhuot\igloo\twig\Extension;
use markhuot\igloo\variables\IglooBehavior;
use yii\base\Event;

/**
 * @property Tree $tree
 */
class Igloo extends Plugin
{

        function init()
        {
            Craft::setAlias('@igloo', $this->getBasePath());

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
                    $event->rules['igloo/slot/<fieldHandle:.+>/edit/<elementId:.+>'] = 'igloo/slot/edit';
                }
            );

            Event::on(
                Element::class,
                Element::EVENT_AFTER_PROPAGATE,
                function (\craft\events\ModelEvent $event) {
                    /** @var Element $component */
                    $component = $event->sender;
                    $iglooAction = \Craft::$app->request->getParam('iglooAction');

                    if ($iglooAction === 'createAndAttach' && $component->isNewForSite) {
                        $fieldHandle = \Craft::$app->request->getParam('iglooSlot');
                        $elementId = \Craft::$app->request->getParam('iglooElement');
                        $element = \Craft::$app->elements->getElementById($elementId);
                        $scope = \Craft::$app->request->getParam('iglooScope');
                        $position = \Craft::$app->request->getParam('iglooPosition');

                        Igloo::getInstance()->tree->attach($element, $fieldHandle, [$component->id], $scope, $position);
                    }
                }
            );

            Event::on(
                CraftVariable::class,
                CraftVariable::EVENT_INIT,
                function(Event $event) {
                    $event->sender->attachBehaviors([
                        IglooBehavior::class,
                    ]);
                }
            );

            Event::on(
                Entry::class,
                Entry::EVENT_DEFINE_BEHAVIORS,
                function (DefineBehaviorsEvent $event) {
                    $event->behaviors[] = SlottedElementBehavior::class;
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
