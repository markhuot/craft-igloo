<?php
namespace markhuot\igloo\fields;

use craft\web\AssetBundle;

class SlotAssetBundle extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@igloo/resources';

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'igloo.js',
        ];

        $this->css = [
            'igloo.css',
        ];

        parent::init();
    }
}
