<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "markhuot\craftpest\test\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use craft\db\Query;
use craft\elements\db\ElementQuery;
use yii\base\Event;

uses(
    markhuot\craftpest\test\TestCase::class,
    markhuot\craftpest\test\RefreshesDatabase::class,
)->in('./');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toBeInDatabase', function ($tableName) {
    $count = (new Query)
        ->from($tableName)
        ->where($this->value)
        ->count();

    return expect((int)$count)->toBeGreaterThan(0);
});

expect()->extend('toNotTouchTheDatabase', function() {
    $eventCallback = function ($event) {
        expect(true)->toBe(false);
    };

    Event::on(ElementQuery::class, ElementQuery::EVENT_BEFORE_PREPARE, $eventCallback);
    $closure = $this->value;
    $closure();
    Event::off(ElementQuery::class, ElementQuery::EVENT_BEFORE_PREPARE, $eventCallback);
    expect(true)->toBe(true);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
