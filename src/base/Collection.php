<?php

namespace markhuot\igloo\base;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use Illuminate\Support\Arr;
use markhuot\igloo\actions\GetStyles;
use Twig\Markup;
use Hashids\Hashids;

class Collection extends \Illuminate\Support\Collection
{
    /** @var FieldInterface */
    protected $field;

    /**
     * @param Collection|ElementInterface[] $items
     */
    public function __construct($items, FieldInterface $field)
    {
        $this->field = $field;
        parent::__construct($items);
    }

    function pluck($value, $key = null)
    {
        return new static(Arr::pluck($this->items, $value, $key), $this->field);
    }

    function filter(callable $callback = null)
    {
        if ($callback) {
            return new static(Arr::where($this->items, $callback), $this->field);
        }

        return new static(array_filter($this->items), $this->field);
    }

    function map(callable $callback)
    {
        return new static(Arr::map($this->items, $callback), $this->field);
    }

    public function values()
    {
        return new static(array_values($this->items), $this->field);
    }

    function render(string $view='default')
    {
        [$markup, $styles] = $this->reduce(function ($carry, $component) {
            $id = 'el' . (new Hashids())->encode($component->id) . (new Hashids())->encode($this->field->id);

            $markup = \Craft::$app->view->renderTemplate(
                '_components/igloo/component', [
                    'id' => $id,
                    'template' => '_components/layouts/hero',
                    'component' => $component,
                ]
            );

            $css = (new GetStyles)->handle($component, $this->field)
                ->map(fn ($style) => $style->getCss())
                ->filter(fn ($s) => !!trim($s))
                ->join('');
            $styles = $css ? '#' . $id . ' {' . $css . '}' : '';

            $carry[0][] = $markup;
            $carry[1][] = $styles;
            return $carry;
        }, []);

        return new Markup(implode('', $markup).'<style type="text/css">' . implode('', $styles) . '</style>', 'utf-8');
    }
}
