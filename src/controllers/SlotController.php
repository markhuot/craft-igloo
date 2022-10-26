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
        $resizable = \Craft::$app->request->getParam('resizable');
        $grid = \Craft::$app->request->getParam('grid');
        $config = (new GetSlotConfig)->handle($field, $element);

        (new UpsertSlotConfig)->handle($field, $element, [
            'columns' => $columns,
            'resizable' => $resizable,
            'grid' => $grid,
            'columnSizes' => $columns === $config['columns'] && $grid === $config['grid'] ? $config['columnSizes'] : null,
        ]);

        $html = \Craft::$app->view->renderTemplate('igloo/fields/slot', [
            'element' => $element,
            'field' => $field,
        ]);

        return $this->asSuccess('Slot saved', [
            'domActions' => [[
                'action' => 'replace',
                'scope' => '[data-element="' . $element->id . '"][data-field="' . $field->id . '"]',
                'html' => $html,
            ]],
        ]);
    }

    function actionStoreColumnSizing()
    {
        $this->requirePostRequest();

        $fieldId = $this->request->getParam('field');
        $field = \Craft::$app->fields->getFieldById($fieldId);
        $elementId = $this->request->getParam('element');
        $element = \Craft::$app->elements->getElementById($elementId);
        $dividerIndex = $this->request->getParam('dividerIndex');
        $left = $this->request->getParam('left');
        $minLeft = $this->request->getParam('minLeft');
        $maxLeft = $this->request->getParam('maxLeft');

        $config = (new GetSlotConfig)->handle($field, $element);
        $config['columnSizes'][$dividerIndex] = ($left - $minLeft) / ($maxLeft - $minLeft);

        (new UpsertSlotConfig)->handle($field, $element, [
            'columnSizes' => $config['columnSizes'],
        ]);

        $html = \Craft::$app->view->renderTemplate('igloo/fields/slot', [
            'element' => $element,
            'field' => $field,
        ]);

        return $this->asSuccess('Column layout saved', [
            'domActions' => [[
                'action' => 'replace',
                'scope' => '[data-element="' . $element->id . '"][data-field="' . $field->id . '"]',
                'html' => $html,
            ]],
        ]);
    }
}
