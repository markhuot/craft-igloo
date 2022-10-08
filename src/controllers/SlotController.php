<?php

namespace markhuot\igloo\controllers;

use craft\base\Element;
use craft\base\Field;
use craft\db\Query;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use markhuot\igloo\actions\GetSlotConfig;
use markhuot\igloo\actions\UpsertSlotConfig;
use markhuot\igloo\db\Table;

class SlotController extends Controller
{
    function actionEdit($elementId, $fieldHandle)
    {
        $element = \Craft::$app->elements->getElementById($elementId);
        $field = \Craft::$app->fields->getFieldByHandle($fieldHandle);
        $config = (new GetSlotConfig)->handle($field, $element);

        return $this->asCpScreen()
            ->title('Edit Slot')
            ->addCrumb('Entries', UrlHelper::cpUrl('entries'))
            ->addCrumb($element->section->name, UrlHelper::cpUrl('entries/' . $element->section->handle))
            ->addCrumb($element->title ?? 'Untitled', $element->cpEditUrl ?? null)
            ->action('igloo/slot/update')
            ->redirectUrl($element->cpEditUrl)
            ->contentTemplate('igloo/slot/edit', [
                'elementId' => $element->id,
                'fieldHandle' => $field->handle,
                'config' => $config
            ]);
    }

    function actionUpdate()
    {
        $this->requirePostRequest();

        $elementId = \Craft::$app->request->getParam('elementId');
        $element = \Craft::$app->elements->getElementById($elementId);
        $fieldHandle = \Craft::$app->request->getParam('fieldHandle');
        $field = \Craft::$app->fields->getFieldByHandle($fieldHandle);
        $columns = \Craft::$app->request->getParam('columns');

        (new UpsertSlotConfig)->handle($field, $element, $columns);

        return $this->asSuccess('Slot saved');
    }
}
