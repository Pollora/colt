<p align="center">
  <a href="https://pollora.dev">
    <img src="https://raw.githubusercontent.com/Pollora/.github/main/brand/banners/colt.png" width="100%" alt="Colt: Eloquent models for the WordPress database">
  </a>
</p>

<p align="center">
  <a href="https://packagist.org/packages/pollora/colt"><img src="https://img.shields.io/packagist/v/pollora/colt" alt="Latest version"></a>
  <a href="https://packagist.org/packages/pollora/colt"><img src="https://img.shields.io/packagist/dt/pollora/colt" alt="Total downloads"></a>
  <a href="https://github.com/Pollora/colt/actions/workflows/ci.yml"><img src="https://github.com/Pollora/colt/actions/workflows/ci.yml/badge.svg" alt="CI"></a>
  <a href="LICENSE"><img src="https://img.shields.io/github/license/Pollora/colt" alt="License"></a>
</p>

Colt is a set of [Eloquent](https://laravel.com/docs/eloquent) models that read and write a WordPress database directly: posts, pages, custom post types, meta, taxonomies, menus, options and users. Use WordPress as the admin and content store, and query its data from Laravel (or any Composer-based PHP app) with the query builder you already know, without loading WordPress.

> Colt is a fork of [Corcel](https://github.com/corcel/corcel), created by [Junior Grossi](https://github.com/jgrossi), kept up to date with current Laravel releases. Corcel's API is preserved, under the `Pollora\Colt` namespace.

> Part of [Pollora](https://pollora.dev), the Laravel framework for WordPress. In a Pollora project it is already installed: use the `Pollora\Models\*` models (`Post`, `Page`, `Term`, `User`…), which extend Colt's.

## Installation

```bash
composer require pollora/colt
```

Requires PHP 8.2+.

| Laravel | Colt |
|:--------|:-----|
| 13.x    | `^10.0` |
| 12.x    | `^10.0` or `^9.0` |

Laravel 11 is covered by the `8.0` branch only (no tagged release). For older Laravel versions, use [Corcel](https://github.com/corcel/corcel).

## Quick start

```php
use Pollora\Colt\Model\Post;

// All published posts
$posts = Post::published()->get();

// A specific post, its title and a custom field
$post = Post::find(31);
echo $post->title;      // alias of post_title
echo $post->meta->link; // value of the "link" post meta
```

## Configuration

### Laravel

Colt registers its service provider through package auto-discovery. Publish the configuration file:

```bash
php artisan vendor:publish --provider="Pollora\Colt\Laravel\ColtServiceProvider"
```

This creates `config/colt.php`, where you set the database connection used for the WordPress tables, and register custom post types and shortcodes.

Suppose `config/database.php` has a connection for WordPress next to the application one:

```php
// config/database.php

'connections' => [

    'mysql' => [ // Laravel database
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'mydatabase',
        'username'  => 'admin',
        'password'  => 'secret',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
        'strict'    => false,
        'engine'    => null,
    ],

    'wordpress' => [ // WordPress database, used by Colt
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'mydatabase',
        'username'  => 'admin',
        'password'  => 'secret',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => 'wp_',
        'strict'    => false,
        'engine'    => null,
    ],
],
```

Then point Colt at it in `config/colt.php`:

```php
'connection' => 'wordpress',
```

### Other PHP frameworks

Load the Composer autoloader if it is not already loaded, then connect to the WordPress database:

```php
require __DIR__ . '/vendor/autoload.php';

Pollora\Colt\Database::connect([
    'database' => 'database_name',
    'username' => 'username',
    'password' => 'pa$$word',
    'prefix'   => 'wp_', // default is 'wp_'
]);
```

You can pass any Eloquent connection parameter. These have defaults you can override:

```php
'driver'    => 'mysql',
'host'      => 'localhost',
'charset'   => 'utf8',
'collation' => 'utf8_unicode_ci',
'prefix'    => 'wp_',
```

## Usage

The examples below use the models of the `Pollora\Colt\Model` namespace (`Post`, `Page`, `Taxonomy`, `Menu`, `Option`, `User`…). If you extend them with your own classes, call your class instead (`App\Models\Post::published()`).

### Posts

```php
// All published posts
$posts = Post::published()->get();
$posts = Post::status('publish')->get();

// A specific post
$post = Post::find(31);
echo $post->post_title;
```

### Your own model classes

Extending `Pollora\Colt\Model\Post` lets you add your own methods and, if needed, use another connection than Colt's default one:

```php
namespace App\Models;

use Pollora\Colt\Model\Post as ColtPost;

class Post extends ColtPost
{
    protected $connection = 'foo-bar';

    public function customMethod()
    {
        //
    }
}
```

```php
$posts = App\Models\Post::all(); // uses the 'foo-bar' connection
```

Extending is optional: the Colt models work as they are.

### Meta data (custom fields)

Read meta values from any post:

```php
$post = Post::find(31);
echo $post->meta->link; // or
echo $post->fields->link; // or
echo $post->link;
```

Create or update meta with `saveMeta()` or `saveField()`. They return a `bool`, like Eloquent's `save()`:

```php
$post = Post::find(1);
$post->saveMeta('username', 'jgrossi');

// Several at once
$post->saveMeta([
    'username' => 'jgrossi',
    'url' => 'http://jgrossi.com',
]);
```

`createMeta()` and `createField()` only create, and return the `PostMeta` instance instead of a `bool`:

```php
$post = Post::find(1);
$postMeta = $post->createMeta('foo', 'bar'); // PostMeta instance
$trueOrFalse = $post->saveMeta('foo', 'baz'); // bool
```

### Querying by meta

Any model using the `MetaFields` trait (`Post`, `User`, `Term`, `Comment`…) has meta scopes.

```php
// A published post that has a meta key
$post = Post::published()->hasMeta('featured_article')->first();

// Matching both key and value
$post = Post::published()->hasMeta('username', 'jgrossi')->first();

// Several fields, or just several keys
$post = Post::hasMeta(['username' => 'jgrossi'])->first();
$post = Post::hasMeta(['username' => 'jgrossi', 'url' => 'jgrossi.com'])->first();
$post = Post::hasMeta(['username', 'url'])->first();
```

`hasMetaLike()` uses SQL `LIKE`: matching is case-insensitive and `%` is a wildcard.

```php
// Matches 'J Grossi', 'J GROSSI' and 'j grossi'
$post = Post::published()->hasMetaLike('author', 'J GROSSI')->first();

// Also matches 'Junior Grossi'
$post = Post::published()->hasMetaLike('author', 'J%GROSSI')->first();
```

### Field aliases

`Post` defines aliases in its static `$aliases` array, such as `title` for `post_title` and `content` for `post_content`:

```php
$post = Post::find(1);
$post->title === $post->post_title; // true
```

Subclasses can add their own; they inherit the parent's:

```php
class A extends \Pollora\Colt\Model\Post
{
    protected static $aliases = [
        'foo' => 'post_foo',
    ];
}

$a = A::find(1);
echo $a->foo;
echo $a->title; // from Post
```

### Ordering and pagination

`Post` and `User` have `newest()` and `oldest()` scopes:

```php
$newest = Post::newest()->first();
$oldest = Post::oldest()->first();
```

Paginate with Eloquent's `paginate()`:

```php
$posts = Post::published()->paginate(5);
```

```blade
{{ $posts->links() }}
```

### Custom post types

Use the `type()` scope, or a class of your own:

```php
// With type()
$videos = Post::type('video')->status('publish')->get();

// With your own class
class Video extends \Pollora\Colt\Model\Post
{
    protected $postType = 'video';
}

$videos = Video::status('publish')->get();
```

`type()` returns `Pollora\Colt\Model\Post` objects; your own class returns `Video` objects, with your methods and properties. Meta works the same way:

```php
$stores = Post::type('store')->status('publish')->take(3)->get();

foreach ($stores as $store) {
    $storeAddress = $store->address; // or $store->meta->address, or $store->fields->address
}
```

#### Returning your class for a post type

By default, `Post::type('video')->first()` returns a `Post`. Map post types to classes and Colt returns your class for that type everywhere, which matters when a collection mixes types (the items of a menu, for example).

In `config/colt.php`:

```php
'post_types' => [
    'video' => App\Models\Video::class,
    'foo' => App\Models\Foo::class,
],
```

Or at runtime:

```php
Post::registerPostType('video', App\Models\Video::class);

// Every item is now a Video instance
$videos = Post::type('video')->status('publish')->get();
```

This also works for the built-in types: register your own class for `page` or `post`.

### Pages

Pages are a post type: use `Post::type('page')` or the `Page` class.

```php
use Pollora\Colt\Model\Page;

$page = Page::slug('about')->first(); // or
$page = Post::type('page')->slug('about')->first();
echo $page->post_title;
```

### Taxonomies and categories

```php
// Taxonomies of a post
$post = Post::find(1);
$taxonomy = $post->taxonomies()->first();
echo $taxonomy->taxonomy;

// Posts in a term
$post = Post::taxonomy('category', 'php')->first();

// A category and its posts
$category = Taxonomy::category()->slug('uncategorized')->first();
$category->posts->each(function ($post) {
    echo $post->post_title;
});

// All categories with their posts
$categories = Taxonomy::where('taxonomy', 'category')->with('posts')->get();
```

### Post format

Like WordPress's `get_post_format()`:

```php
echo $post->getFormat(); // 'video', etc.
```

### Attachments and revisions

```php
$page = Page::slug('about')->with('attachment')->first();
print_r($page->attachment); // featured image

$post = Post::slug('test')->with('revision')->first();
print_r($post->revision); // all revisions
```

### Thumbnails

```php
$post = Post::find(1);

// A Pollora\Colt\Model\Meta\ThumbnailMeta instance
print_r($post->thumbnail);

// Cast to string, it is the URL of the original image
echo $post->thumbnail;
```

`size()` returns the metadata of a generated size (e.g. `thumbnail` or `medium`), or the original image URL when that size does not exist:

```php
if ($post->thumbnail !== null) {
    /**
     * [
     *     'file' => 'filename-300x300.jpg',
     *     'width' => 300,
     *     'height' => 300,
     *     'mime-type' => 'image/jpeg',
     *     'url' => 'http://localhost/wp-content/uploads/filename-300x300.jpg',
     * ]
     */
    print_r($post->thumbnail->size(Pollora\Colt\Model\Meta\ThumbnailMeta::SIZE_THUMBNAIL));

    // http://localhost/wp-content/uploads/filename.jpg
    print_r($post->thumbnail->size('invalid_size'));
}
```

### Shortcodes

Registered shortcodes in `post_content` are rendered when you read `$post->content`.

#### From the configuration (Laravel)

Map shortcodes to classes under the `shortcodes` key of `config/colt.php`. Each class implements `Pollora\Colt\Shortcode`, which requires a `render()` method:

```php
'shortcodes' => [
    'foo' => App\Shortcodes\FooShortcode::class,
    'bar' => App\Shortcodes\BarShortcode::class,
],
```

```php
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class FooShortcode implements \Pollora\Colt\Shortcode
{
    public function render(ShortcodeInterface $shortcode)
    {
        return sprintf(
            'html-for-shortcode-%s-%s',
            $shortcode->getName(),
            $shortcode->getParameter('one')
        );
    }
}
```

#### At runtime

```php
// [gallery id="1"]
Post::addShortcode('gallery', function ($shortcode) {
    return $shortcode->getName() . '.' . $shortcode->getParameter('id');
});

$post = Post::find(1);
echo $post->content;
```

In Laravel, register runtime shortcodes in the `boot()` method of a service provider, such as `App\Providers\AppServiceProvider`.

#### Parser

Shortcodes are parsed with [thunderer/shortcode](https://github.com/thunderer/Shortcode). The default `RegularParser` suits most cases. If parsing looks off, switch to `WordpressParser`, which follows WordPress's shortcode regex more closely, or to a parser of your own, in `config/colt.php`:

```php
'shortcode_parser' => Thunder\Shortcode\Parser\RegularParser::class,
// 'shortcode_parser' => Thunder\Shortcode\Parser\WordpressParser::class,
```

Outside Laravel, call `setShortcodeParser()` on any model using the `Shortcodes` trait, such as `Post`:

```php
$post->setShortcodeParser(new WordpressParser());
echo $post->content; // parsed with WordpressParser
```

### Options

`Option` reads and writes the `wp_options` table:

```php
$siteUrl = Option::get('siteurl');

Option::add('foo', 'bar'); // stored as a string
Option::add('baz', ['one' => 'two']); // serialized

$options = Option::asArray();
echo $options['siteurl'];

$options = Option::asArray(['siteurl', 'home', 'blogname']);
echo $options['home'];
```

### Menus

Get a menu by its slug. Its `items` are a collection of `MenuItem` objects (posts of type `nav_menu_item`). Supported items are pages, posts, custom links and categories; `instance()` returns the object behind an item:

```php
$menu = Menu::slug('primary')->first();

foreach ($menu->items as $item) {
    echo $item->instance()->title;     // a Post
    echo $item->instance()->name;      // a Term
    echo $item->instance()->link_text; // a custom link
}
```

`instance()` returns a `Post` for `post` items, a `Page` for `page` items, a `CustomLink` for `custom` items and a `Term` for `category` items.

#### Multi-level menus

`MenuItem::parent()` returns the parent of an item (`Post`, `Page`, `CustomLink` or `Term`):

```php
$items = Menu::slug('foo')->first()->items;
$parent = $items->first()->parent();
```

To build levels, group the items by parent with the collection's [`groupBy()`](https://laravel.com/docs/collections#method-groupby), on `$item->parent()->ID`.

### Users

```php
$users = User::get();

$user = User::find(1);
echo $user->user_login;
```

### Authentication

#### With Laravel

Colt registers a `colt` user provider. Declare it in `config/auth.php` so Laravel logs in WordPress users:

```php
'providers' => [
    'users' => [
        'driver' => 'colt',
        'model'  => Pollora\Colt\Model\User::class,
    ],
],
```

```php
Auth::validate([
    'email' => 'admin@example.com', // or 'username'
    'password' => 'secret',
]);
```

For password resets, the `Pollora\Colt\Laravel\Auth\ResetsPasswords` trait provides a `resetPassword()` method that stores the new password with WordPress's hashing. Use it in the class that resets passwords, in place of Laravel's own implementation:

```php
use Pollora\Colt\Laravel\Auth\ResetsPasswords as ColtResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords, ColtResetsPasswords {
        ColtResetsPasswords::resetPassword insteadof ResetsPasswords;
    }
}
```

#### Without Laravel

Authenticate with `AuthUserProvider` directly. Both `username` and `email` work as credentials:

```php
$userProvider = new Pollora\Colt\Laravel\Auth\AuthUserProvider;
$user = $userProvider->retrieveByCredentials(['username' => 'admin']);

if (! is_null($user) && $userProvider->validateCredentials($user, ['password' => 'admin'])) {
    // logged in
}
```

## Documentation

How Colt relates to Corcel and to Pollora's models: [pollora.dev/compare](https://pollora.dev/compare/).

## Testing

```bash
composer test
```

The suite uses Pest with an in-memory SQLite database, built from the factories and migrations in `tests/database`.

A second suite runs against a real WordPress installation, in `.wordpress` or the path set in `WP_PATH` (see the `wordpress` job of `.github/workflows/ci.yml`):

```bash
composer test:wordpress
```

## Contributing

Contributions are welcome: see the [contributing guide](https://github.com/Pollora/.github/blob/main/CONTRIBUTING.md). Report security issues privately, as described in the [security policy](https://github.com/Pollora/.github/blob/main/SECURITY.md).

## License

Colt is open-source software licensed under the [MIT license](LICENSE). © [RuBee group](https://rubee.group)
