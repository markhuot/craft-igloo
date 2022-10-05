<?php

namespace markhuot\igloo\controllers;

use craft\base\Element;
use craft\base\Field;
use craft\elements\Entry;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use markhuot\igloo\Igloo;

class ContentController extends Controller
{
    /** @var Element */
    protected $element;

    /** @var Field */
    protected $field;

    /**
     * Any content actions require an element and a field to act on. For example attaching
     * a new component to a slot in an element or detaching an existing component from a
     * slot. This unifies access to the element and field in one place.
     */
    function beforeAction($action): bool
    {
        $elementId = \Craft::$app->request->getParam('elementId');
        $this->element = \Craft::$app->elements->getElementById($elementId);

        $fieldHandle = \Craft::$app->request->getParam('fieldHandle');
        $this->field = \Craft::$app->fields->getFieldByHandle($fieldHandle);

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
                'fieldHandle' => $this->field->handle,
            ]);
    }

    /**
     * The post request to attach a component to an element.
     */
    function actionAttach()
    {
        $this->requirePostRequest();

        $elements = \Craft::$app->request->getParam('elements');
        if (empty($elements)) {
            return $this->asSuccess('No content selected', []);
        }

        Igloo::getInstance()->tree->attach($this->element, $this->field, $elements);

        $templates = collect($elements)
            ->map(fn ($componentId) => \Craft::$app->view->renderTemplate('igloo/fields/_leaf.twig', [
                'leaf' => \Craft::$app->elements->getElementById($componentId),
                'actionData' => [
                    'elementId' => $this->element->id,
                    'fieldHandle' => $this->field->handle,
                ]
            ]));

        $domActions = $templates->map(fn ($template) => [
            'action' => 'insert',
            'scope' => '[data-uid="' . $this->field->uid . '"]',
            'position' => 'beforeend',
            'html' => $template,
        ]);

        return $this->asSuccess('Content attached', [
            'domActions' => $domActions,
        ]);
    }

    /**
     * The post request to detach a component from an element.
     */
    function actionDetach()
    {
        $this->requirePostRequest();

        $elements = \Craft::$app->request->getParam('elements');
        if (empty($elements)) {
            return $this->asSuccess('No content selected', []);
        }

        $elementUids = collect($elements)->map(fn ($id) => \Craft::$app->elements->getElementById($id))->pluck('uid');
        Igloo::getInstance()->tree->detach($this->element, $this->field, $elements);

        return $this->asSuccess('Content detached', [
            'domActions' => $elementUids->map(fn ($uid) => [
                'action' => 'remove',
                'scope' => '[data-uid="' . $uid . '"]'
            ])->toArray(),
        ]);
    }
}
