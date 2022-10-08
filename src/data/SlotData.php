<?php

namespace markhuot\igloo\data;

class SlotData
{
    public $id;
    public $elementId;
    public $slot;
    public $componentId;
    public $lft;
    public $rgt;
    public $uid;

    function __construct(array $slotRecord=null)
    {
        if ($slotRecord) {
            $this->id = $slotRecord['id'];
            $this->elementId = $slotRecord['elementId'];
            $this->slot = $slotRecord['slot'];
            $this->componentId = $slotRecord['componentId'];
            $this->lft = $slotRecord['lft'];
            $this->rgt = $slotRecord['rgt'];
            $this->uid = $slotRecord['uid'];
        }
    }
}
