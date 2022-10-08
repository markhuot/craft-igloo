<?php

namespace markhuot\igloo\behaviors;

use markhuot\igloo\data\SlotData;
use yii\base\Behavior;

class SlottedElementBehavior extends Behavior
{
    protected SlotData $slot;

    function init()
    {
        $this->slot = new SlotData;
    }

    function setSlot(SlotData|array $slotData)
    {
        if (is_array($slotData)) {
            $this->slot = new SlotData($slotData);
        }
        else if (is_a($slotData, SlotData::class)) {
            $this->slot = $slotData;
        }
        else {
            throw new \Exception('Unknown slot data');
        }
    }

    function getSlot()
    {
        return $this->slot;
    }
}
