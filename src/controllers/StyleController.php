<?php

namespace markhuot\igloo\controllers;

use craft\elements\Entry;
use craft\helpers\UrlHelper;
use craft\web\Controller;

class StyleController extends Controller
{
    function actionEdit(int $elementId, ?int $fieldId, ?string $slot=null)
    {
        $element = \Craft::$app->elements->getElementById($elementId);
        $field = \Craft::$app->fields->getFieldById($fieldId);

        return $this->asCpScreen()
            ->title('Select content')
            ->addCrumb('Entries', UrlHelper::cpUrl('entries'))
            ->addCrumb($element->section->name, UrlHelper::cpUrl('entries/' . $element->section->handle))
            ->addCrumb($element->title ?? 'Untitled', $element->cpEditUrl ?? null)
            ->action('igloo/style/update')
            ->redirectUrl($element->cpEditUrl)
            ->contentTemplate('igloo/styles/edit', [
                'element' => $element,
                'field' => $field,
                'slot' => $slot,
            ]);
    }

    function actionUpdate()
    {
        $this->requirePostRequest();

        $elementId = \Craft::$app->request->getParam('elementId');
        $element = \Craft::$app->elements->getElementById($elementId);
        $fieldId = \Craft::$app->request->getParam('fieldId');
        $field = \Craft::$app->fields->getFieldById($fieldId);
        $slot = \Craft::$app->request->getParam('slot');

        return $this->asSuccess('Styles saved');
    }
}
