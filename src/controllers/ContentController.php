<?php

namespace markhuot\igloo\controllers;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\db\Query;
use craft\db\Table as DbTable;
use craft\elements\Entry;
use craft\helpers\Db;
use craft\helpers\ElementHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use markhuot\igloo\actions\GetLeavesForComponent;
use markhuot\igloo\actions\RemapDraftComponents;
use markhuot\igloo\data\SlotData;
use markhuot\igloo\db\Table;
use markhuot\igloo\Igloo;
use yii\db\conditions\OrCondition;

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

        // If we're interacting with an element it should always be on a draft version of that
        // element. So, create a draft now for us to work on if it doesn't already exist.
        if ($this->request->isActionRequest && !$this->element->isDraft && !$this->element->isProvisionalDraft) {
            $draft = \Craft::$app->drafts->createDraft($this->element, \Craft::$app->user->identity->id, null, null, [], true);
            $draft->setCanonical($this->element);
            $this->element = $draft;
        }

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
        $elements = \Craft::$app->request->getParam('elements', []);

        Igloo::getInstance()->tree->attach($this->element, $this->field, $this->slot, $elements, $scope, $position);

        return $this->asIglooSuccess($this->element, $this->field, 'Content attached');
    }

    /**
     * The post request to detach a component from an element.
     */
    function actionDetach()
    {
        $this->requirePostRequest();

        $componentIds = \Craft::$app->request->getParam('elements', []);

        $remap = new RemapDraftComponents;
        if ($remap->needsRemapping($this->element, $componentIds)) {
            $componentIds = $remap->handle($this->element, $componentIds);
        }

        Igloo::getInstance()->tree->detach($this->element, $this->field, $this->slot, $componentIds);

        return $this->asIglooSuccess($this->element, $this->field, 'Content detached');
    }

    /**
     * Response with Igloo specific JSON
     */
    protected function asIglooSuccess(ElementInterface $element, FieldInterface $field, string $message)
    {
        $html = \Craft::$app->view->renderTemplate('igloo/fields/slot', [
            'element' => $element,
            'field' => $this->field,
        ]);

        return $this->asSuccess($message, [
            'events' => [
                ['name' => 'createDraft', 'detail' => ['provisional' => $element->isProvisionalDraft]],
                ['name' => 'markChanged', 'detail' => ['handle' => $this->field->handle]],
            ],
            'domActions' => [
                ['action' => 'replace', 'scope' => '[data-element="' . $this->element->id . '"][data-field="' . $this->field->id . '"]', 'html' => $html],
            ]
        ]);
    }
}
