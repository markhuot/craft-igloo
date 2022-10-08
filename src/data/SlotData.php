<?php

namespace markhuot\igloo\data;

class SlotData
{
    public int $id;
    public int $parentId;
    public int $fieldId;
    public string $slot;
    public int $childId;
    public int $sort;
    public \DateTime $dateCreated;
    public string $uid;

    function __construct(array $slotRecord=null)
    {
        if ($slotRecord) {
            $this->id = (int)$slotRecord['id'];
            $this->parentId = (int)$slotRecord['parentId'];
            $this->fieldId = (int)$slotRecord['fieldId'];
            $this->slot = $slotRecord['slot'];
            $this->childId = (int)$slotRecord['childId'];
            $this->sort = (int)$slotRecord['sort'];
            $this->dateCreated = new \DateTime($slotRecord['dateCreated'], new \DateTimeZone('UTC'));
            $this->uid = $slotRecord['uid'];
        }
    }
}
