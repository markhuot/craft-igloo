<?php

namespace markhuot\igloo\styles;

use craft\helpers\StringHelper;
use Illuminate\Support\Collection;
use Twig\Markup;

class Style
{
    protected Collection $selectors;

    static function definition()
    {
        return [];
    }

    function __construct(?array $data=[])
    {
        $this->selectors = collect(static::definition())
            ->mapWithKeys(function ($config, $name) use ($data) {
                if (is_numeric($name)) {
                    $name = $config;
                    $config = [];
                }

                return [$name => array_merge($config, [
                    'name' => $config['name'] ?? $name,
                    'inputName' => $config['inputName'] ?? 'styles[' . $name . ']',
                    'label' => $config['label'] ?? StringHelper::toTitleCase(preg_replace('/([a-z])([A-Z])/', '$1 $2', $name)),
                    'attribute' => $config['attribute'] ?? StringHelper::toKebabCase(preg_replace('/([a-z])([A-Z])/', '$1 $2', $name)),
                    'template' => $config['template'] ?? 'igloo/components/text-field',
                    'value' => $data[$name] ?? null,
                ])];
            });
    }

    function getCss()
    {
        return $this->selectors
            ->filter(fn ($s) => $s['value'])
            ->map(fn ($s) => "{$s['attribute']}: {$s['value']};")
            ->join('');
    }

    function getInputHtml()
    {
        return new Markup($this->selectors
            ->map(fn ($s) => \Craft::$app->view->renderTemplate($s['template'], array_merge(
                $s,
                $this->getInputVars($s['name'])
            )))
            ->join("\n"), 'utf-8');
    }

    protected function getInputVars(string $name)
    {
        return [];
    }
}
