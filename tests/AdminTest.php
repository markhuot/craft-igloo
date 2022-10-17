<?php

use markhuot\craftpest\factories\Entry;
use markhuot\craftpest\factories\Field;
use markhuot\craftpest\factories\Section;
use markhuot\craftpest\factories\User;
use markhuot\igloo\fields\Slot;

it('has a content link', function () {
    $field = Field::factory()
        ->type(Slot::class)
        ->create();

    $section = Section::factory()
        ->fields($field)
        ->create();

    $entry = Entry::factory()
        ->section($section)
        ->create();

    $user = User::factory()
        ->admin(true)
        ->create();

    var_dump($entry->cpEditUrl);

    $this->actingAs($user)
        ->get($entry->cpEditUrl)
        ->assertOk();
});