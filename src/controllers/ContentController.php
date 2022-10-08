<?php

namespace markhuot\igloo\controllers;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\elements\Entry;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use markhuot\igloo\actions\GetLeavesForComponent;
use markhuot\igloo\data\SlotData;
use markhuot\igloo\Igloo;

class ContentController extends Controller
{
    /** @var ElementInterface */
    protected $element;

    /** @var FieldInterface */
    protected $field;

    /** @var string */
    protected $slot;

    /**
     * Any content actions require an element and a field to act on. For example attaching
     * a new component to a field in an element or detaching an existing component from a
     * field. This unifies access to the element and field in one place.
     */
    function beforeAction($action): bool
    {
        $elementId = \Craft::$app->request->getParam('elementId');
        $this->element = \Craft::$app->elements->getElementById($elementId);

        $fieldId = \Craft::$app->request->getParam('fieldId');
        $this->field = \Craft::$app->fields->getFieldById($fieldId);

        $this->slot = \Craft::$app->request->getParam('slot');

        return parent::beforeAction($action);
    }

    /**
     * The default view for attaching a component to a slot. This allows you to create
     * a new component via a slideout or re-use an existing content piece already in
     * the system.
     */
    function actionIndex()
    {
        return $this->asCpScreen()
            ->title('Select content')
            ->addCrumb('Entries', UrlHelper::cpUrl('entries'))
            ->addCrumb($this->element->section->name, UrlHelper::cpUrl('entries/' . $this->element->section->handle))
            ->addCrumb($this->element->title ?? 'Untitled', $this->element->cpEditUrl ?? null)
            ->action('igloo/content/attach')
            ->redirectUrl($this->element->cpEditUrl)
            ->contentTemplate('igloo/content/index', [
                'elements' => Entry::find()->limit(100)->all(),
                'element' => $this->element,
                'field' => $this->field,
                'slot' => $this->slot,
                'scope' => $this->request->getParam('scope'),
                'position' => $this->request->getParam('position'),
            ]);
    }

    /**
     * The post request to attach a component to an element.
     */
    function actionAttach()
    {
        $this->requirePostRequest();

        $scope = \Craft::$app->request->getParam('scope');
        $position = \Craft::$app->request->getParam('position');

        $elements = \Craft::$app->request->getParam('elements');
        if (empty($elements)) {
            return $this->asSuccess('No content selected', []);
        }

        $slots = Igloo::getInstance()->tree->attach($this->element, $this->field, $this->slot, $elements, $scope, $position);

        // $domActions = collect($slots)
        //     ->map(function (SlotData $slot) use ($element, $slotName) {
        //         $component = \Craft::$app->elements->getElementById($slot->componentId);
        //         $component->setSlot($slot);
        //
        //         $template = \Craft::$app->view->renderTemplate('igloo/fields/_leaf.twig', [
        //             'element' => $element,
        //             'slot' => $slotName,
        //             'leaf' => $component,
        //         ]);
        //
        //         return [$component, $template];
        //     })
        //     ->map(fn ($props) => [
        //         'action' => 'insert',
        //         'scope' => empty($scope) ? '[data-element="' . $element . '"][data-slot="' . $slotName . '"]' : '[data-uid="' . $scope . '"]',
        //         'position' => $position,
        //         'html' => $props[1],
        //     ]);

        $html = \Craft::$app->view->renderTemplate('igloo/fields/slot', [
            'element' => $this->element,
            'field' => $this->field,
        ]);

        return $this->asSuccess('Content attached', [
            //'domActions' => $domActions,
            'domActions' => [
                ['action' => 'replace', 'scope' => '[data-element="' . $this->element->id . '"][data-field="' . $this->field->id . '"]', 'html' => $html],
            ]
        ]);
    }

    /**
     * The post request to detach a component from an element.
     */
    function actionDetach()
    {
        $this->requirePostRequest();

        $elementUids = \Craft::$app->request->getParam('elements');
        if (empty($elementUids)) {
            return $this->asSuccess('No content selected', []);
        }

        Igloo::getInstance()->tree->detach($this->element, $this->field, $this->slot, $elementUids);

        $html = \Craft::$app->view->renderTemplate('igloo/fields/slot', [
            'element' => $this->element,
            'field' => $this->field,
        ]);

        return $this->asSuccess('Content detached', [
            'domActions' => collect($elementUids)->map(fn ($uid) => [
                'action' => 'replace',
                'scope' => '[data-element="' . $this->element->id . '"][data-field="' . $this->field->id . '"]',
                'html' => $html,
            ])->toArray(),
        ]);
    }
}
