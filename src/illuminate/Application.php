<?php

namespace markhuot\igloo\illuminate;

class Application extends \Illuminate\Foundation\Application
{
    function getNamespace()
    {
        return 'markhuot\\igloo\\';
    }

    public function path($path = '')
    {
        $path = str(parent::path($path))->replaceMatches('/\/app$/', '/src');
        return $path;
    }
}
