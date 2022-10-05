<?php

namespace markhuot\igloo\twig;

use Twig\Extension\ExtensionInterface;
use Twig\Markup;
use Twig\TwigFunction;

class Extension implements ExtensionInterface
{
    function getFunctions(): array
    {
        return [
            // new TwigFunction('iglooResource', [$this, 'iglooResource']),
        ];
    }

    // function iglooResource($src)
    // {
    //     $hotFilepath = \Craft::getAlias('@webroot') . '/hot';
    //
    //     return new Markup(file_exists($hotFilepath) ?
    //         $this->getHotResource($src) :
    //         $this->getBuiltResource($src), 'utf-8');
    // }

    // function getHotResource($src) {
    //     $hotFilepath = \Craft::getAlias('@webroot') . '/hot';
    //     $hotUrl = file_get_contents($hotFilepath);
    //
    //     return '<script type="module" src="' . implode('/', [
    //         rtrim($hotUrl, '/'),
    //         'src',
    //         ltrim($src, '/'),
    //     ]) . '"></script>';
    // }

    // function getBuiltResource($src) {
    //     return '';
    // }

    public function getTokenParsers()
    {
        return [];
    }

    public function getNodeVisitors()
    {
        return [];
    }

    public function getFilters()
    {
        return [];
    }

    public function getTests()
    {
        return [];
    }

    public function getOperators()
    {
        return [];
    }
}
