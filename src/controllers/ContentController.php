<?php

namespace markhuot\igloo\controllers;

use craft\base\Element;
use craft\base\Field;
use craft\elements\Entry;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use markhuot\igloo\actions\GetLeavesForComponent;
use markhuot\igloo\data\SlotData;
use markhuot\igloo\Igloo;

class ContentController extends Controller
{
    /** @var Element */
    protected $element;

    /** @var string */
    protected $slot;

    /**
     * Any content actions require an element and a slot to act on. For example attaching
     * a new component to a slot in an element or detaching an existing component from a
     * slot. This unifies access to the element and field in one place.
     */
    function beforeAction($action): bool
    {
        $elementId = \Craft::$app->request->getParam('elementId');
        $this->element = \Craft::$app->elements->getElementById($elementId);

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
                'elementType' => get_class($this->element),
                'elementId' => $this->element->id,
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

        $element = $this->element;
        $slotName = $this->slot;

        $elements = \Craft::$app->request->getParam('elements');
        if (empty($elements)) {
            return $this->asSuccess('No content selected', []);
        }

        $scope = \Craft::$app->request->getParam('scope');
        $position = \Craft::$app->request->getParam('position');

        if (empty($scope)) {
            $leaves = (new GetLeavesForComponent())->handle($element);
            if (count($leaves)) {
                $element = \Craft::$app->elements->getElementById($leaves[0]->elementId);
                $slotName = $leaves[0]->slot;
                $scope = $leaves[0]->uid;
            }
        }

        $slots = Igloo::getInstance()->tree->attach($element, $slotName, $elements, $scope, $position);

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

        $field = \Craft::$app->fields->getFieldByHandle($slotName);
        $html = \Craft::$app->view->renderTemplate('igloo/fields/slot', [
            'element' => $element,
            'field' => $field,
        ]);

        return $this->asSuccess('Content attached', [
            //'domActions' => $domActions,
            'domActions' => [
                ['action' => 'replace', 'scope' => '[data-element="' . $element->id . '"][data-field="' . $field->id . '"]', 'html' => $html],
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

        Igloo::getInstance()->tree->detach($this->element, $this->slot, $elementUids);

        $field = \Craft::$app->fields->getFieldByHandle($this->slot);
        $html = \Craft::$app->view->renderTemplate('igloo/fields/slot', [
            'element' => $this->element,
            'field' => $field,
        ]);

        return $this->asSuccess('Content detached', [
            'domActions' => collect($elementUids)->map(fn ($uid) => [
                'action' => 'replace',
                'scope' => '[data-element="' . $this->element->id . '"][data-field="' . $field->id . '"]',
                'html' => $html,
            ])->toArray(),
        ]);
    }
}
