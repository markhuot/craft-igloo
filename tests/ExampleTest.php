<?php

use function markhuot\craftpest\helpers\http\get;
use markhuot\craftpest\factories\{Section,Entry,Field};
use markhuot\igloo\fields\Slot;
use markhuot\igloo\Igloo;

it('stores a component', function () {
    $field = Field::factory()->type(Slot::class)->create();
    $section = Section::factory()->fields($field)->create();
    $child = Entry::factory()->section($section)->create();
    $parent = Entry::factory()->section($section)->create();

    $slots = Igloo::getInstance()->tree->attach($parent, $field, 'default', [$child->id], null, 'beforeend');
    expect($slots)->toHaveCount(1);
});
