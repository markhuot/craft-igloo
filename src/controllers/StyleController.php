<?php

namespace markhuot\igloo\controllers;

use craft\db\Query;
use craft\elements\Entry;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use markhuot\igloo\actions\GetStyles;
use markhuot\igloo\actions\UpsertStyles;
use markhuot\igloo\db\Table;

class StyleController extends Controller
{
    function actionEdit(int $elementId, ?int $fieldId)
    {
        $element = \Craft::$app->elements->getElementById($elementId);
        $field = \Craft::$app->fields->getFieldById($fieldId);
        $styles = (new GetStyles)->handle($element, $field);

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
                'styles' => $styles,
            ]);
    }

    function actionUpdate()
    {
        $this->requirePostRequest();

        $elementId = \Craft::$app->request->getParam('elementId');
        $element = \Craft::$app->elements->getElementById($elementId);
        $fieldId = \Craft::$app->request->getParam('fieldId');
        $field = \Craft::$app->fields->getFieldById($fieldId);

        (new UpsertStyles)->handle($element, $field, $this->request->getParam('styles'));

        return $this->asSuccess('Styles saved');
    }
}
