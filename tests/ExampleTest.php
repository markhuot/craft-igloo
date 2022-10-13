<?php

use function markhuot\craftpest\helpers\http\get;
use markhuot\craftpest\factories\{Section,Entry,Field};
use markhuot\igloo\db\Table;
use markhuot\igloo\fields\Slot;
use markhuot\igloo\Igloo;

dataset('section and field', fn() => yield function () {
    $field = Field::factory()->type(Slot::class)->create();
    $section = Section::factory()->fields($field)->create();
    return [$field, $section];
});

it('stores a component', function ($props) {
    [$field, $section] = $props;
    $child = Entry::factory()->section($section)->create();
    $parent = Entry::factory()->section($section)->create();
    
    $slots = Igloo::getInstance()->tree->attach($parent, $field, 'default', [$child->id], null, 'beforeend');
    expect($slots)->toHaveCount(1);
})->with('section and field');

it('stores component ordering', function ($props) {
    [$field, $section] = $props;
    $children = Entry::factory()->section($section)->count(3)->create();
    $parent = Entry::factory()->section($section)->create();
    
    $slots = Igloo::getInstance()->tree->attach($parent, $field, 'default', $children->pluck('id')->toArray(), null, 'beforeend');
    expect(collect($slots)->pluck('sort')->toArray())->toEqualCanonicalizing([0,1,2]);
})->with('section and field');

it('stores closure table', function($props) {
    [$field, $section] = $props;
    [$grandchild, $child, $parent] = Entry::factory()->section($section)->count(3)->create();

    Igloo::getInstance()->tree->attach($child, $field, 'default', [$grandchild->id], null, 'beforeend');
    Igloo::getInstance()->tree->attach($parent, $field, 'default', [$child->id], null, 'beforeend');

    expect(['ancestor' => $child->id, 'descendant' => $grandchild->id])->toBeInDatabase(Table::COMPONENTS_PATHS);
    expect(['ancestor' => $parent->id, 'descendant' => $child->id])->toBeInDatabase(Table::COMPONENTS_PATHS);
})->with('section and field')->only();
