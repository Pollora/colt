<?php

use Pollora\Colt\Model\CustomLink;
use Pollora\Colt\Model\Menu;
use Pollora\Colt\Model\MenuItem;
use Pollora\Colt\Model\Post;
use Pollora\Colt\Model\Taxonomy;

test('it has the correct class name', function () {
    $menu = factory(Menu::class)->create();

    expect($menu)->toBeInstanceOf(Menu::class);
});

test('it has integer id', function () {
    $menus = factory(Menu::class, 2)->create();

    collect($menus)->each(function ($menu) {
        expect($menu)->not->toBeNull();
        expect($menu->term_taxonomy_id)->toBeInt();
    });
});

test('it can be queried by slug', function () {
    factory(Menu::class)->create();
    $menu = Menu::slug('foo')->first();

    expect(count($menu->items))->toBeGreaterThanOrEqual(0);
});

test('it has items as posts', function () {
    $menu = createMenu();

    expect($menu->items)->toHaveCount(2);

    collect($menu->items)->each(function ($post) {
        expect($post)->not->toBeNull();
        expect($post)->toBeInstanceOf(MenuItem::class);
        expect($post)->toBeInstanceOf(Post::class);
    });
});

test('it can have multilevel children', function () {
    $menu = createMenu();

    $parent = $menu->posts->first();
    $child = $menu->posts->last();

    expect($parent)->not->toBeNull();
    expect($child)->not->toBeNull();
    expect($child->meta->_menu_item_menu_item_parent)->toEqual($parent->ID);
    expect($parent->_menu_item_menu_item_parent)->toEqual(0);
});

test('it has parent relation', function () {
    $menu = createComplexMenu();

    $posts = $menu->items->filter(function ($item) {
        return $item->meta->_menu_item_object === 'post';
    });

    $parent = $posts->first()->instance();
    $child = $posts->last();

    expect($child->parent()->ID)->toEqual($parent->ID);
    expect($child->parent()->post_name)->toEqual($parent->post_name);
});

test('it can have custom links associated as meta', function () {
    $item = factory(MenuItem::class)->create([
        'post_title' => 'Foobar',
    ]);

    $item->saveMeta([
        '_menu_item_type' => 'custom',
        '_menu_item_menu_item_parent' => 0,
        '_menu_item_object_id' => $item->ID,
        '_menu_item_object' => 'custom',
        '_menu_item_target' => '',
        '_menu_item_classes' => 'a:1:{i:0;s:0:"";}',
        '_menu_item_xfn' => '',
        '_menu_item_url' => 'http://example.com',
    ]);

    expect($item->post_title)->toEqual('Foobar');
    expect($item->instance()->link_text)->toEqual('Foobar');
    expect($item->meta->_menu_item_url)->toEqual('http://example.com');
    expect($item->instance()->url)->toEqual('http://example.com');
});

test('it can have pages', function () {
    $menu = createComplexMenu();

    $pages = $menu->items->filter(function ($item) {
        return $item->meta->_menu_item_object === 'page';
    });

    $pages->each(function (MenuItem $item) {
        expect($item->instance()->post_title)->toEqual("page-title");
        expect($item->instance()->post_content)->toEqual("page-content");
    });
});

test('it can have posts', function () {
    $menu = createComplexMenu();

    $posts = $menu->items->filter(function ($item) {
        return $item->meta->_menu_item_object === 'post';
    });

    $posts->each(function (MenuItem $item) {
        expect($item->instance()->title)->toEqual("post-title");
        expect($item->instance()->content)->toEqual("post-content");
    });
});

test('it can have custom links', function () {
    $menu = createComplexMenu();

    $posts = $menu->items->filter(function ($item) {
        return $item->meta->_menu_item_object === 'custom';
    });

    $posts->each(function (MenuItem $item) {
        expect($item->instance()->url)->toEqual("http://example.com");
        expect($item->instance()->link_text)->toEqual("custom-link-text");
    });
});

test('it can have categories', function () {
    $menu = createComplexMenu();

    $posts = $menu->items->filter(function ($item) {
        return $item->meta->_menu_item_object === 'category';
    });

    $posts->each(function (MenuItem $item) {
        expect($item->instance()->name)->toEqual("Bar");
        expect($item->instance()->slug)->toEqual("bar");
    });
});

function createMenu(): Menu
{
    $parent = factory(Post::class)->create(['post_type' => 'nav_menu_item']);
    $parent->saveMeta('_menu_item_menu_item_parent', 0);

    $child = factory(Post::class)->create(['post_type' => 'nav_menu_item']);
    $child->saveMeta('_menu_item_menu_item_parent', $parent->ID);

    return tap(factory(Menu::class)->create(), function ($menu) use ($parent, $child) {
        $menu->posts()->attach([$parent->ID, $child->ID]);
    });
}

function createComplexMenu(): Menu
{
    $menu = factory(Menu::class)->create();

    buildPage($menu);

    $post = buildPost($menu);
    buildPost($menu, $post->ID);

    buildCustomLink($menu);
    buildCategory($menu);

    return $menu;
}

function buildPage(Menu $menu): void
{
    $page = factory(Post::class)->create([
        'post_type' => 'page',
        'post_title' => 'page-title',
        'post_content' => 'page-content',
    ]);

    $item = factory(MenuItem::class)->create();

    $item->saveMeta([
        '_menu_item_type' => 'post_type',
        '_menu_item_menu_item_parent' => 0,
        '_menu_item_object_id' => $page->ID,
        '_menu_item_object' => $page->post_type,
        '_menu_item_target' => '',
        '_menu_item_classes' => 'a:1:{i:0;s:0:"";}',
        '_menu_item_xfn' => '',
        '_menu_item_url' => '',
    ]);

    $menu->items()->save($item);
}

function buildPost(Menu $menu, int $parentId = 0): Post
{
    $post = factory(Post::class)->create([
        'post_title' => 'post-title',
        'post_content' => 'post-content',
    ]);

    $item = factory(MenuItem::class)->create();

    $item->saveMeta([
        '_menu_item_type' => 'post_type',
        '_menu_item_menu_item_parent' => $parentId,
        '_menu_item_object_id' => $post->ID,
        '_menu_item_object' => $post->post_type,
        '_menu_item_target' => '',
        '_menu_item_classes' => 'a:1:{i:0;s:0:"";}',
        '_menu_item_xfn' => '',
        '_menu_item_url' => '',
    ]);

    $menu->items()->save($item);

    return $post;
}

function buildCustomLink(Menu $menu): void
{
    $link = factory(CustomLink::class)->create([
        'post_title' => 'custom-link-text',
    ]);

    $link->saveMeta([
        '_menu_item_type' => 'custom',
        '_menu_item_menu_item_parent' => 0,
        '_menu_item_object_id' => $link->ID,
        '_menu_item_object' => 'custom',
        '_menu_item_target' => '',
        '_menu_item_classes' => 'a:1:{i:0;s:0:"";}',
        '_menu_item_xfn' => '',
        '_menu_item_url' => 'http://example.com',
    ]);

    $menu->items()->save($link);
}

function buildCategory(Menu $menu): void
{
    $taxonomy = factory(Taxonomy::class)->create([
        'taxonomy' => 'category',
    ]);

    $item = factory(MenuItem::class)->create();

    $item->saveMeta([
        '_menu_item_type' => 'taxonomy',
        '_menu_item_menu_item_parent' => 0,
        '_menu_item_object_id' => $taxonomy->term_taxonomy_id,
        '_menu_item_object' => 'category',
        '_menu_item_target' => '',
        '_menu_item_classes' => 'a:1:{i:0;s:0:"";}',
        '_menu_item_xfn' => '',
        '_menu_item_url' => '',
    ]);

    $menu->items()->save($item);
}
