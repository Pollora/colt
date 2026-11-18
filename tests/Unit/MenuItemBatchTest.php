<?php

declare(strict_types=1);

/*
 * Behaviour of the menu item batch (MenuItemCollection + MenuItem) that the
 * query-count tests do not pin: which items are skipped, which references are
 * ignored, and what a second read costs.
 */

use Illuminate\Support\Facades\DB;
use Pollora\Colt\Model\Collection\MenuItemCollection;
use Pollora\Colt\Model\Menu;
use Pollora\Colt\Model\MenuItem;
use Pollora\Colt\Model\Post;
use Pollora\Colt\Tests\Support\ColtFixtures;
use Pollora\Colt\Tests\Support\QueryCounter;

const COLT_OBJECT_ID = '_menu_item_object_id';

it('skips what is not a menu item in a menu item collection', function (): void {
    ColtFixtures::menu(1);
    $item = MenuItem::first();

    $collection = new MenuItemCollection([new Post, $item]);
    $collection->resolveTargets(COLT_OBJECT_ID);

    expect($item->instance())->toBeInstanceOf(Post::class)
        ->and($item->instance()->title)->toBe('target 1');
});

it('keeps resolving the next items after one that is already resolved', function (): void {
    ColtFixtures::menu(2);
    [$first, $second] = MenuItem::orderBy('menu_order')->get()->all();

    $collection = new MenuItemCollection([$first, $second]);
    $first->storeResolvedTarget(COLT_OBJECT_ID, null);
    $collection->resolveTargets(COLT_OBJECT_ID);

    $queriesToRead = QueryCounter::count(fn () => $second->instance());

    // Served by the batch run above: the item that follows the resolved one was not skipped.
    expect($queriesToRead)->toBe(0);
});

it('does not query for an item with no target to look up: :dataset', function (Closure $break): void {
    ColtFixtures::menu(1);
    $item = MenuItem::first();
    $break($item);
    $item = MenuItem::first();
    $collection = new MenuItemCollection([$item]);

    $queries = QueryCounter::count(fn () => $collection->resolveTargets(COLT_OBJECT_ID));

    expect($item->targetReference(COLT_OBJECT_ID))->toBeNull()
        ->and($queries)->toBe(0)
        ->and(QueryCounter::count(fn () => $item->instance()))->toBe(0)
        ->and($item->instance())->toBeNull();
})->with([
    'no object id' => [fn () => DB::table('postmeta')->where('meta_key', COLT_OBJECT_ID)->delete()],
    'empty object id' => [fn () => DB::table('postmeta')->where('meta_key', COLT_OBJECT_ID)->update(['meta_value' => ''])],
    'unknown object type' => [fn () => DB::table('postmeta')->where('meta_key', '_menu_item_object')->update(['meta_value' => 'nope'])],
]);

it('keeps resolving the next items after one that has nothing to look up', function (): void {
    ColtFixtures::menu(2);
    [$first, $second] = MenuItem::orderBy('menu_order')->get()->all();
    DB::table('postmeta')->where('post_id', $first->ID)->where('meta_key', COLT_OBJECT_ID)->delete();
    [$first, $second] = MenuItem::orderBy('menu_order')->get()->all();

    (new MenuItemCollection([$first, $second]))->resolveTargets(COLT_OBJECT_ID);

    expect(QueryCounter::count(fn () => $second->instance()))->toBe(0)
        ->and($second->instance()->title)->toBe('target 2');
});

it('gives the class and id of the target of a menu item', function (): void {
    ColtFixtures::menu(1);
    $item = MenuItem::first();

    expect($item->targetReference(COLT_OBJECT_ID))->toBe([Post::class, $item->meta->_menu_item_object_id]);
});

it('looks the target up on its own once the batch value was read', function (): void {
    ColtFixtures::menu(3);
    $items = Menu::first()->items;
    $items->each(fn (MenuItem $item) => $item->instance());

    $second = QueryCounter::count(fn () => $items->each(fn (MenuItem $item) => $item->instance()));

    // One own lookup per item (target + its meta): the batch is not rebuilt.
    expect($second)->toBe(6);
});
