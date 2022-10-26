<?php

use markhuot\craftpest\factories\Entry;
use markhuot\craftpest\factories\Field;
use markhuot\craftpest\factories\Section;
use markhuot\craftpest\factories\User;
use markhuot\igloo\fields\Slot;

dataset('an igloo field', function () { yield function () {
    $field = Field::factory()
        ->type(Slot::class)
        ->create();

    $section = Section::factory()
        ->fields($field)
        ->create();

    // $entry = Entry::factory()
    //     ->section($section)
    //     ->create();

    $user = User::factory()
        ->admin(true)
        ->create();

    $this->actingAs($user);

    return [$field, $section, $user];
};});

it('attaches content, while making a draft', function ($props) {
    [$field, $section, $user] = $props;

    $entry = Entry::factory()->section($section)->create();

    expect($entry)
        ->isDraft->toBeFalse()
        ->isProvisionalDraft->toBeFalse();

    $this->get($entry->cpEditUrl)
        ->assertOk();

    $this->action('igloo/content/attach', [

    ])
        ->assertOk();
})->with('an igloo field')->only();

it('detaches content, while makimg a draft', function () {

});