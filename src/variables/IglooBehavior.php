<?php

namespace markhuot\igloo\variables;

use yii\base\Behavior;

class IglooBehavior extends Behavior
{
    function igloo()
    {
        return (new IglooVariable);
    }
}
