<?php

namespace markhuot\igloo\styles;

use Twig\Markup;

class Sizing extends Style
{
    static function definition()
    {
        return [
            'width',
            'height',
            'marginTop',
            'marginRight',
            'marginBottom',
            'marginLeft',
            'paddingTop',
            'paddingRight',
            'paddingBottom',
            'paddingLeft',
        ];
    }

    function getInputHtml()
    {
        return new Markup(\Craft::$app->view->renderTemplate('igloo/components/sizing', [
            'selectors' => $this->selectors,
        ]), 'utf-8');
    }
}
