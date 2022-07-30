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

    function beforeAction($action): bool
    {
        $elementId = \Craft::$app->request->getParam('elementId');
        $this->element = \Craft::$app->elements->getElementById($elementId);

        $fieldHandle = \Craft::$app->request->getParam('fieldHandle');
        $this->field = \Craft::$app->fields->getFieldByHandle($fieldHandle);

        return parent::beforeAction($action);
    }

    function actionIndex()
    {
        return $this->asCpScreen()
            ->title('Select content')
            ->addCrumb('Entries', UrlHelper::cpUrl('entries'))
            ->addCrumb($this->element->section->name, UrlHelper::cpUrl('entries/' . $this->element->section->handle))
            ->addCrumb($this->element->title, $this->element->cpEditUrl ?? null)
            ->action('igloo/content/attach')
            ->redirectUrl($this->element->cpEditUrl)
            ->contentTemplate('igloo/_content/index', [
                'elements' => Entry::find()->limit(100)->all(),
                'elementType' => get_class($this->element),
                'elementId' => $this->element->id,
                'fieldHandle' => $this->field->handle,
            ]);
    }

    function actionAttach()
    {
        $this->requirePostRequest();

        $elements = \Craft::$app->request->getParam('elements');
        if (empty($elements)) {
            return $this->asSuccess('No content selected', []);
        }

        Igloo::getInstance()->tree->attach($this->element, $this->field, $elements);

        return $this->asSuccess('Content attached', []);
    }

    function actionDetach()
    {
        $this->requirePostRequest();

        return $this->asSuccess('Content detached');
    }
}
