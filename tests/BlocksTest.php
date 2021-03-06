<?php

use craft\helpers\StringHelper;
use function Spatie\Snapshots\assertMatchesSnapshot;

it('prepares block for save', function () {
    $block = new \markhuot\igloo\models\Text('foo bar baz');
    assertMatchesSnapshot($block->serialize());
});

it('supports css classlist', function () {
    $block = new \markhuot\igloo\models\Text();
    $block->attributes->classlist->add('foo');
    expect($block->attributes->classlist)->toContain('foo');
    expect($block->attributes->classlist->contains('foo'))->toBeTrue();
    $block->attributes->classlist->remove('foo');
    expect($block->attributes->classlist)->not->toContain('foo');
    expect($block->attributes->classlist->contains('foo'))->toBeFalse();
    $block->attributes->classlist->toggle('foo');
    expect($block->attributes->classlist)->toContain('foo');
    $block->attributes->classlist->toggle('foo');
    expect($block->attributes->classlist)->not->toContain('foo');
    $block->attributes->classlist->add('foo');
    $block->attributes->classlist->replace('foo', 'bar');
    expect($block->attributes->classlist)->not->toContain('foo');
    expect($block->attributes->classlist)->toContain('bar');
    expect($block->attributes->className)->toBe('bar');
});

it('prepares styled block for save', function () {
    $block = new \markhuot\igloo\models\Text('foo bar', ['attributes' => ['style' => ['color' => 'red']]]);
    assertMatchesSnapshot($block->serialize());
});

it('prepares block children for save', function () {
    $box = new \markhuot\igloo\models\Box();
    $box->append(new \markhuot\igloo\models\Text('foo'));
    $box->append(new \markhuot\igloo\models\Text('bar'));
    assertMatchesSnapshot($box->flatten()->serialize());
});

it('supports default children', function () {
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->append(new \markhuot\igloo\models\Text);
    $tree->append(new \markhuot\igloo\models\Blockquote);
    $tree->append(new \markhuot\igloo\models\Text);
    $records = $tree->flatten()->serialize();
    $tree2 = (new \markhuot\igloo\services\Blocks)->hydrateRecords($records);
    expect($records)->toBe($tree2->flatten()->serialize());
});

it('flattens tree', function () {
    $box = new \markhuot\igloo\models\Box();
    $box->append(new \markhuot\igloo\models\Text('foo'));
    $box->append(new \markhuot\igloo\models\Text('bar'));
    assertMatchesSnapshot($box->flatten()->serialize());
});

it('flattens a tree with named children', function () {
    $blockquote = new \markhuot\igloo\models\Blockquote();
    $blockquote->content[0] = new \markhuot\igloo\models\Text('foo');
    $blockquote->author[0] = new \markhuot\igloo\models\Text('bar');
    $records = $blockquote->flatten()->serialize();
    assertMatchesSnapshot($records);
});

it('flattens deep tree', function () {
    $box = new \markhuot\igloo\models\Box();
    $box->append(new \markhuot\igloo\models\Text('foo'));
    $box->append((new \markhuot\igloo\models\Box())
        ->append(new \markhuot\igloo\models\Text('baz'))
        ->append(new \markhuot\igloo\models\Text('qux'))
        ->append(new \markhuot\igloo\models\Text('qid'))
    );
    $box->append(new \markhuot\igloo\models\Text('bar'));
    $records = $box->flatten()->serialize();
    assertMatchesSnapshot($records);
});

it('creates a tree', function () {
    $records = [
        ['{{%igloo_block_structure}}' => ['lft' => 0, 'rgt' => 9]],
        ['{{%igloo_block_structure}}' => ['lft' => 1, 'rgt' => 2]],
        ['{{%igloo_block_structure}}' => ['lft' => 3, 'rgt' => 6]],
        ['{{%igloo_block_structure}}' => ['lft' => 4, 'rgt' => 5]],
        ['{{%igloo_block_structure}}' => ['lft' => 7, 'rgt' => 8]],
    ];
    $tree = (new \markhuot\igloo\services\Blocks())->makeTree($records);
    assertMatchesSnapshot($records);
});

it('hydrates a record', function () {
    $record = [
        '{{%igloo_blocks}}' => [
            'type' => \markhuot\igloo\models\Text::class,
        ],
        '{{%igloo_content_text}}' => [
            'content' => 'foo bar baz'
        ],
    ];
    $block = (new \markhuot\igloo\services\Blocks())->hydrate($record);
    assertMatchesSnapshot($block);
});

it('hydrates traits', function () {
    $record = [
        '{{%igloo_blocks}}' => [
            'type' => \markhuot\igloo\models\Text::class,
        ],
        '{{%igloo_content_text}}' => [
            'content' => 'foo bar baz',
        ],
        '{{%igloo_block_attributes}}' => [
            'data' => '{"style":{"color":"red"}}',
        ],
    ];
    $block = (new \markhuot\igloo\services\Blocks())->hydrate($record);
    assertMatchesSnapshot($block);
});

it('hydrates record children', function () {
    $blockquote = new \markhuot\igloo\models\Blockquote;
    $blockquote->content->append(new \markhuot\igloo\models\Text('To be or not to be...'));
    $blockquote->author->append(new \markhuot\igloo\models\Text('Some Guy'));

    $box = new \markhuot\igloo\models\Box;
    $box->append(new \markhuot\igloo\models\Text);
    $box->append($blockquote);
    $box->append(new \markhuot\igloo\models\Text);
    // dump($box->flatten()->serialize());
    $block = (new \markhuot\igloo\services\Blocks())->hydrateRecords($box->flatten()->serialize());
    // dump($block->flatten()->serialize());
    expect($box->flatten()->serialize())->toEqual($block->flatten()->serialize());
});

it('hydrates slotted block collections', function () {
    $blockquote = new \markhuot\igloo\models\Blockquote;
    $records = $blockquote->flatten()->serialize();
    expect($records[0]['{{%igloo_block_structure}}']['slot'])->toBe(null);
    expect($records[1]['{{%igloo_block_structure}}']['slot'])->toBe('content');
    expect($records[2]['{{%igloo_block_structure}}']['slot'])->toBe('author');

    $tree = (new \markhuot\igloo\services\Blocks)->hydrateRecords($records);
    //dd($tree->flatten()->serialize());
    expect($tree->flatten()->serialize())->toBe($records);

    //dd(get_class($tree->getAtPath('0.author.0')->collection));
    expect(get_class($tree->getAtPath('0.author.0')->collection))->toBe(\markhuot\igloo\base\SlottedBlockCollection::class);
});

it('saves a single record', function () {
    $box = new \markhuot\igloo\models\Text('foo bar');
    $records = $box->flatten()->serialize();
    $tree = uniqid();
    $records = (new \markhuot\igloo\services\Blocks())->saveRecords($records, $tree);
    $result = (new \craft\db\Query)
        ->from(['b' => '{{%igloo_blocks}}'])
        ->innerJoin('{{%igloo_block_structure}} s', 's.id=b.id')
        ->leftJoin('{{%igloo_content_text}} t', 't.id=b.id')
        ->where(['s.tree' => $tree])
        ->all();
    $result = collect($result)
        ->map(function ($row) {
            unset($row['id']);
            unset($row['uid']);
            unset($row['dateCreated']);
            unset($row['dateUpdated']);
            unset($row['tree']);
            return $row;
        })
        ->toArray();
    assertMatchesSnapshot($result);
});

it('saves a simple tree', function () {
    $box = new \markhuot\igloo\models\Box();
    $box->append(new \markhuot\igloo\models\Text('foo'));
    $box->append(new \markhuot\igloo\models\Text('bar'));
    $records = $box->flatten()->serialize();
    $tree = uniqid();
    (new \markhuot\igloo\services\Blocks())->saveRecords($records, $tree);
    $result = (new \craft\db\Query)
        ->from(['b' => '{{%igloo_blocks}}'])
        ->innerJoin('{{%igloo_block_structure}} s', 's.id=b.id')
        ->leftJoin('{{%igloo_content_text}} t', 't.id=b.id')
        ->where(['s.tree' => $tree])
        ->all();
    $result = collect($result)
        ->map(function ($row) {
            unset($row['id']);
            unset($row['uid']);
            unset($row['dateCreated']);
            unset($row['dateUpdated']);
            unset($row['tree']);
            return $row;
        })
        ->toArray();
        assertMatchesSnapshot($result);
});

it('resaves an existing block', function () {
    $text = new \markhuot\igloo\models\Text('foo');
    (new \markhuot\igloo\services\Blocks())->saveBlock($text);
    expect($text->id)->not->toBeEmpty();
    
    $text = (new \markhuot\igloo\services\Blocks())->getBlock($text->id);
    expect($text->content)->toBe('foo');
    
    $text->content = 'foo bar';
    (new \markhuot\igloo\services\Blocks())->saveBlock($text);
    expect($text->id)->not->toBeEmpty();
    
    $text = (new \markhuot\igloo\services\Blocks())->getBlock($text->id);
    expect($text->content)->toBe('foo bar');
});

it('resaves an existing block without affecting nested set', function () {
    $box = new \markhuot\igloo\models\Box();
    $box->append(new \markhuot\igloo\models\Box());
    $box->append($child = new \markhuot\igloo\models\Box());
    $box->append(new \markhuot\igloo\models\Box());
    (new \markhuot\igloo\services\Blocks())->saveBlock($box);

    expect($child->id)->not->toBeEmpty();
    expect($previousLft = $child->lft)->not->toBeEmpty();
    
    (new \markhuot\igloo\services\Blocks())->saveBlock($child);
    expect($child->lft)->toBe($previousLft); 
});

it('retrieves a tree', function () {
    $tree = uniqid();
    $box = new \markhuot\igloo\models\Box();
    $box->append(new \markhuot\igloo\models\Text());
    $box->append(new \markhuot\igloo\models\Text());
    (new \markhuot\igloo\services\Blocks())->saveBlock($box, $tree);
    $tree = (new \markhuot\igloo\services\Blocks())->getTree($tree)->first();
    expect($box->flatten()->serialize())->toEqual($tree->flatten()->serialize());
});

it('retrieves a block', function () {
    $box = new \markhuot\igloo\models\Box();
    $box->append(new \markhuot\igloo\models\Text('foo bar'));
    (new \markhuot\igloo\services\Blocks())->saveBlock($box);
    expect($box->id)->not->toBeEmpty();
    $fetchedBox = (new \markhuot\igloo\services\Blocks())->getBlock($box->id);
    expect($fetchedBox->flatten()->serialize())->toEqual($box->flatten()->serialize());
});

it('retrieves a block from a tree with the correct lft/rgt', function () {
    $parent = new \markhuot\igloo\models\Box;
    $parent->append($child = new \markhuot\igloo\models\Box);
    (new \markhuot\igloo\services\Blocks)->saveBlock($parent);

    $fetchedChild = (new \markhuot\igloo\services\Blocks)->getBlock($child->id);
    expect($child->lft)->toBe($fetchedChild->lft);
    expect($child->rgt)->toBe($fetchedChild->rgt);
});

it('fills data', function () {
    $text = new \markhuot\igloo\models\Text('foo');
    $text->fill(['content' => 'foo bar']);
    assertMatchesSnapshot($text);
});

it('saves styles', function () {
    $text = new \markhuot\igloo\models\Text('foo');
    $text->attributes->style->fontSize = '28px';
    (new \markhuot\igloo\services\Blocks())->saveBlock($text);

    $text = (new \markhuot\igloo\services\Blocks())->getBlock($text->id);
    expect($text->attributes->style->fontSize)->toBe('28px');
});

it('appends a block to a tree', function () {
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->append(new \markhuot\igloo\models\Text('foo'));
    $tree->append(new \markhuot\igloo\models\Text('bar'));
    expect($tree[0]->content)->toBe('foo');
    expect($tree[0]->lft)->toBe(0);
    expect($tree[0]->rgt)->toBe(1);
    expect($tree[1]->content)->toBe('bar');
    expect($tree[1]->lft)->toBe(2);
    expect($tree[1]->rgt)->toBe(3);
    assertMatchesSnapshot($tree->anonymize()->flatten()->serialize());
});

it('prepends a block to a tree', function () {
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->prepend(new \markhuot\igloo\models\Text('bar'));
    $tree->prepend(new \markhuot\igloo\models\Text('foo'));
    expect($tree[0]->content)->toBe('foo');
    expect($tree[0]->lft)->toBe(0);
    expect($tree[0]->rgt)->toBe(1);
    expect($tree[1]->content)->toBe('bar');
    expect($tree[1]->lft)->toBe(2);
    expect($tree[1]->rgt)->toBe(3);
    assertMatchesSnapshot($tree->anonymize()->flatten()->serialize());
});

it('inserts a block to a specific place', function () {
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->append(new \markhuot\igloo\models\Text('foo'));
    $tree->append(new \markhuot\igloo\models\Text('baz'));
    $tree->insertAtIndex(new \markhuot\igloo\models\Text('bar'), 1);
    expect($tree[1]->content)->toBe('bar');
    assertMatchesSnapshot($tree->anonymize()->flatten()->serialize());
});

it('inserts a deeply nested block', function () {
    $greatGrandParent = new \markhuot\igloo\models\Text('greatGrandParent');
    $grandParent = new \markhuot\igloo\models\Text('grandParent');
    $parent = new \markhuot\igloo\models\Text('parent');
    $child = new \markhuot\igloo\models\Text('child');
    $grandChild = new \markhuot\igloo\models\Text('grandChild');
    $greatGrandParent->children->append(
        $grandParent->children->append(
            $parent->children->append(
                $child->children->append($grandChild)->block
            )->block
        )->block
    );
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->append($greatGrandParent);
    $secondGrandParent = new \markhuot\igloo\models\Text('secondGreatGrandParent');
    $tree->append($secondGrandParent);
    $parent->children->append(new \markhuot\igloo\models\Text('second child'));
    expect($secondGrandParent->lft)->toBe(12);
});

it('deletes a block from memory', function () {
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->append($one = new \markhuot\igloo\models\Text('one'));
    $tree->append($two = new \markhuot\igloo\models\Text('two'));
    $tree->append($three = new \markhuot\igloo\models\Text('three'));
    $tree->deleteAtIndex(1);
    
    expect($three->lft)->toBe(2);
    expect($three->rgt)->toBe(3);
});

it('deletes a block with children from memory', function () {
    $grandparent = new \markhuot\igloo\models\Box;
    $grandparent->append($parent = new \markhuot\igloo\models\Box);
    
    $parent->append(new \markhuot\igloo\models\Text);
    $parent->append((new \markhuot\igloo\models\Blockquote)
        ->content->append(new \markhuot\igloo\models\Text)->block
        ->author->append(new \markhuot\igloo\models\Text)->block);
    $parent->append(new \markhuot\igloo\models\Text);

    $grandparent->append(new \markhuot\igloo\models\Box);

    $parent->children->deleteAtIndex(1);

    assertMatchesSnapshot($grandparent->anonymize()->flatten()->serialize());
});

it('stores tombstones on deletion', function () {
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->append(new \markhuot\igloo\models\Text('parent one'));
    $tree->append($box = new \markhuot\igloo\models\Box());
    $tree->append(new \markhuot\igloo\models\Text('parent three'));

    $box->children->append(new \markhuot\igloo\models\Text('child one'));
    $box->children->append($childTwo = new \markhuot\igloo\models\Text('child two - delete me', ['id' => 123]));
    $box->children->deleteAtIndex(1);

    expect($tree->getTombstonesFromTree())->toBe([$childTwo]);
});

it('deletes a block from the database', function () {
    $treeId = uniqid();
    $tree = new \markhuot\igloo\base\BlockCollection($treeId);
    $tree->append($one = new \markhuot\igloo\models\Text('one'));
    $tree->append($two = new \markhuot\igloo\models\Text('two'));
    $tree->append($three = new \markhuot\igloo\models\Text('three'));
    (new \markhuot\igloo\services\Blocks)->saveTree($tree);
    
    $tree->deleteAtIndex(1);
    (new \markhuot\igloo\services\Blocks)->saveTree($tree);
    
    $fetchedTree = (new \markhuot\igloo\services\Blocks)->getTree($treeId);
    expect($fetchedTree->count())->toBe(2);
});

it('doesn\'t store tombstones for unsaved blocks', function () {
    $treeId = uniqid();
    $tree = new \markhuot\igloo\base\BlockCollection($treeId);
    $tree->append($one = new \markhuot\igloo\models\Text('one'));
    $tree->append($two = new \markhuot\igloo\models\Text('two'));
    $tree->append($three = new \markhuot\igloo\models\Text('three'));

    $tree->deleteAtIndex(1);
    expect(count($tree->getTombstones()))->toBe(0);
});

it('clears tombstones during move', function () {
    $tree = new \markhuot\igloo\base\BlockCollection();
    $tree->append($one = new \markhuot\igloo\models\Text('one', ['id' => 1]));
    $tree->append($two = new \markhuot\igloo\models\Text('two', ['id' => 2]));
    $tree->append($three = new \markhuot\igloo\models\Text('three', ['id' => 3]));
    // (new \markhuot\igloo\services\Blocks)->saveTree($tree);
    
    $tree->deleteAtIndex(1);
    expect(count($tree->getTombstonesFromTree()))->toBe(1);

    $tree->insertAtIndex($two, 1);
    expect(count($tree->getTombstonesFromTree()))->toBe(0);
});

it('gets the root from a block', function () {
    $tree = new \markhuot\igloo\base\BlockCollection();
    $tree->append($grandparent = new \markhuot\igloo\models\Box);
    $grandparent->append($parent = new \markhuot\igloo\models\Box);
    $parent->append($child = new \markhuot\igloo\models\Box);
    $child->append($grandchild = new \markhuot\igloo\models\Box);

    expect($grandchild->getRoot())->toBe($tree);
});

it('clears tombstones during deep move', function () {
    $treeId = uniqid();
    $tree = new \markhuot\igloo\base\BlockCollection($treeId);
    $tree->append(new \markhuot\igloo\models\Text('one'));
    $tree->append($box1 = new \markhuot\igloo\models\Box);
    $tree->append($box2 = new \markhuot\igloo\models\Box);
    $tree->append(new \markhuot\igloo\models\Text('three'));

    $box1->append($text = new \markhuot\igloo\models\Text('two'));
    (new \markhuot\igloo\services\Blocks)->saveTree($tree);
    
    $box1->children->deleteAtIndex(0);
    expect(count($tree->getTombstonesFromTree()))->toBe(1);
    
    $box2->children->append($text);
    expect(count($tree->getTombstonesFromTree()))->toBe(0);
});

it('finds block index in collection', function () {
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->append($foo = new \markhuot\igloo\models\Text('foo'));
    $tree->append($bar = new \markhuot\igloo\models\Text('bar'));
    $tree->append($baz = new \markhuot\igloo\models\Text('baz'));
    expect($tree->getIndexOfBlock($baz))->toBe(2);
    expect($tree->getBlocksAfterIndex(1)->toArray())->toBe([$bar, $baz]);
});

it('allows block (and slotted block) collection array access and iterator', function () {
    $blockquote = new \markhuot\igloo\models\Blockquote;
    $blockquote->content[0] = ($content = new \markhuot\igloo\models\Text('content'));
    $blockquote->author[0] = ($author = new \markhuot\igloo\models\Text('author'));

    expect($blockquote->content->count())->toBe(1);
    expect($blockquote->content[0])->toBe($content);
    foreach ($blockquote->content as $child) {
        expect($child)->toBe($content);
    }

    expect(count($blockquote->children))->toBe(2);
    expect($blockquote->children[1])->toBe($author);
});

it('parses a path', function () {
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->append(new \markhuot\igloo\models\Text('one'));
    $tree->append($parent = new \markhuot\igloo\models\Box);
    $parent->children->append($child = new \markhuot\igloo\models\Box);

    $result = $tree->getAtPath('1.children.0');
    expect($result)->toBe($child);

    $result = $tree->getAtPath('1.children');
    expect($result)->toBe($parent->getSlot('children'));
});

it('parses a slotted path', function () {
    $blockquote = new \markhuot\igloo\models\Blockquote;
    $blockquote->content[0] = new \markhuot\igloo\models\Text('one');
    $blockquote->author[0] = new \markhuot\igloo\models\Text('two');
    $blockquote->content[1] = new \markhuot\igloo\models\Text('three');
    
    assertMatchesSnapshot($blockquote->flatten()->map(function ($b) { return $b->getPath(); }));
});

it('moves a block', function () {
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->append(new \markhuot\igloo\models\Text('one'));
    $tree->append(new \markhuot\igloo\models\Text('two'));
    $tree->append(new \markhuot\igloo\models\Text('three'));
    $tree->moveBlock('0', '3');
    
    expect($tree[0]->content)->toBe('two');
    expect($tree[1]->content)->toBe('three');
    expect($tree[2]->content)->toBe('one');
    assertMatchesSnapshot($tree->flatten()->serialize());
});

it('moves a block deeper in to a tree', function () {
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->append(new \markhuot\igloo\models\Text('one'));
    $tree->append($parent = new \markhuot\igloo\models\Box);
    $parent->children->append($child = new \markhuot\igloo\models\Box);
    $tree->moveBlock('0', '1.children.0.children.0');
    
    expect($tree->count())->toBe(1);
    expect($child->children->count())->toBe(1);
    assertMatchesSnapshot($tree->flatten()->serialize());
});

it('updates block slots when moving', function () {
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->append($blockquote = new \markhuot\igloo\models\Blockquote);
    $blockquote->content[0] = new \markhuot\igloo\models\Text('one');
    $blockquote->author[0] = new \markhuot\igloo\models\Text('two');
    $blockquote->content[1] = new \markhuot\igloo\models\Text('three');

    $tree->moveBlock('0.content.1', '0');
    
    assertMatchesSnapshot($tree->flatten()->serialize());
});

it('moves a block within a nested slot', function () {
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->append($blockquote = new \markhuot\igloo\models\Blockquote);
    $blockquote->content[0] = new \markhuot\igloo\models\Text('one');
    $blockquote->author[0] = new \markhuot\igloo\models\Text('two');
    $blockquote->content[1] = new \markhuot\igloo\models\Text('three');
    
    $tree->moveBlock('0.content.1', '0.content.0');
    
    assertMatchesSnapshot($tree->flatten()->serialize());
});

it('creates the correct path for null slots', function () {
    $tree = new \markhuot\igloo\base\BlockCollection;
    $tree->append($box = new \markhuot\igloo\models\Box);
    $box->append($text = new \markhuot\igloo\models\Text('foo'));

    expect($text->getPath())->toBe('0.children.0');
});

it('deletes nested blocks', function () {
    $treeId = uniqid();
    $tree = new \markhuot\igloo\base\BlockCollection($treeId);
    $tree->append($box = new \markhuot\igloo\models\Box);
    $box->append($blockquote = new \markhuot\igloo\models\Blockquote);
    $tree->append($text = new \markhuot\igloo\models\Text('postscript'));

    (new \markhuot\igloo\services\Blocks)->saveTree($tree);

    $tree->getAtPath('0.children.0.content')->deleteAtIndex(0);
    expect(count($tree->getTombstonesFromTree()))->toBe(1);

    // Ensures that the ->saveTree() call _actually_ removes the deeply nested element from
    // the database. Previously `->saveTree` was calling `->getTombstones()` which meant it
    // only removed tombstones that were deleted from the top of the tree. It has been fixed
    // to call `getTombstonesFromTree` such that it deletes tombstones from anywhere in
    // the tree
    (new \markhuot\igloo\services\Blocks)->saveTree($tree);

    $newTree = (new \markhuot\igloo\services\Blocks)->getTree($tree);
    assertMatchesSnapshot($newTree->anonymize()->flatten()->serialize());
});