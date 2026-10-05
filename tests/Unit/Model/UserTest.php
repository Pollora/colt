<?php

use Carbon\Carbon;
use Pollora\Colt\Tests\Unit\Model\Customer;
use Pollora\Colt\Model\User;
use Pollora\Colt\Model\Collection\MetaCollection;
use Pollora\Colt\Model\Comment;
use Pollora\Colt\Model\Post;

test('it is instance of user', function () {
    $user = factory(User::class)->create();

    expect($user)->toBeInstanceOf(User::class);
});

test('it has the correct id', function () {
    $user = factory(User::class)->create(['ID' => 20]);

    expect($user)->not->toBeNull();
    expect($user->ID)->toEqual(20);
});

test('it can be ordered', function () {
    $date = Carbon::now()->subYear();

    $first = factory(User::class)->create(['user_registered' => $date]);
    $last = factory(User::class)->create(['user_registered' => $date->addMonth()]);

    $newest = User::newest()->first();
    $oldest = User::oldest()->first();

    expect($oldest->ID)->toEqual($first->ID);
    expect($newest->ID)->toEqual($last->ID);
});

test('it has multiple property aliases', function () {
    $user = factory(User::class)->create();
    $user->saveMeta('nickname', 'foo');
    $user->saveMeta('first_name', 'bar');
    $user->saveMeta('last_name', 'baz');

    expect('baz')->toEqual($user->last_name);
    expect($user->login)->toEqual($user->user_login);
    expect($user->email)->toEqual($user->user_email);
    expect($user->slug)->toEqual($user->user_nicename);
    expect($user->url)->toEqual($user->user_url);
    expect($user->nickname)->toEqual($user->meta->nickname);
    expect($user->first_name)->toEqual($user->meta->first_name);
    expect($user->last_name)->toEqual($user->meta->last_name);
    expect($user->created_at)->toEqual($user->user_registered);
});

test('it has the correct auth identifier', function () {
    $user = factory(User::class)->create();

    expect($user->getAuthIdentifier())->toEqual($user->ID);
});

test('it can add meta', function () {
    $user = factory(User::class)->create();

    $user->saveMeta('foo', 'bar');

    expect($user->meta)->not->toBeEmpty();
    expect($user->fields)->not->toBeEmpty();
    expect($user->meta)->toBeInstanceOf(MetaCollection::class);
});

test('it can update meta', function () {
    $user = factory(User::class)->create();

    $user->saveMeta('foo', 'bar');
    $user->saveField('foo', 'baz');

    expect('baz')->toEqual($user->meta->foo);
});

test('it can update multiples metas', function () {
    $user = factory(User::class)->create();

    $user->createMeta(['foo' => 'bar', 'fee' => 'baz']);

    expect($user->meta->foo)->toEqual('bar');
    expect($user->meta->fee)->toEqual('baz');

    $user->saveMeta(['foo' => 'baz', 'fee' => 'bar']);

    expect($user->meta->foo)->toEqual('baz');
    expect($user->meta->fee)->toEqual('bar');
});

test('it can have a different database connection', function () {
    $user = factory(User::class)->make();
    $user->setConnection('foo');
    $user->save();

    $user->createMeta('fee', 'baz');

    expect($user->getConnectionName())->toEqual('foo');

    $user->meta->each(function ($meta) {
        expect($meta->getConnectionName())->toEqual('foo');
    });
});

test('it has meta scope with empty meta', function () {
    $id = factory(User::class)->create()->ID;

    $user = (new User())->newQuery()
        ->where('ID', $id)
        ->hasMeta('foo', 'bar')
        ->first();

    expect($user)->toBeEmpty();
});

test('it has meta scope with valid meta', function () {
    $user = factory(User::class)->create();
    $user->saveMeta('foo', 'bar');

    $validUser = (new User())->newQuery()
        ->where('ID', $user->ID)
        ->hasMeta('foo', 'bar')
        ->first();

    expect($validUser)->not->toBeEmpty();
});

test('it has avatar', function () {
    $user = factory(User::class)->create();

    expect($user->avatar)->toEqual('//secure.gravatar.com/avatar/e64c7d89f26bd1972efa854d13d7dd61?d=mm');
});

test('it has not avatar', function () {
    $user = factory(User::class)->create(['user_email' => '']);

    expect($user->avatar)->toEqual('//secure.gravatar.com/avatar/?d=mm');
});

test('it children has correct meta relation', function () {
    $post = factory(Post::class)->create();
    $post->createMeta('foo', 'bar');
    $user = factory(User::class)->create();
    $user->createMeta('bar', 'foo');

    $customer = new Customer();
    $customer->ID = $user->ID;

    // post ID and customer ID are same
    expect($customer->ID)->toEqual($post->ID);
    expect($customer->meta->bar)->toEqual('foo');
    expect($customer->meta->foo)->toBeNull();
});

test('missing relations', function () {
    $user = factory(User::class)->create();

    factory(Post::class, 2)->create(['post_author' => $user->ID]);
    factory(Comment::class, 3)->create(['user_id' => $user->ID]);

    expect($user->posts)->toHaveCount(2);
    expect($user->comments)->toHaveCount(3);
});

test('timestamps methods', function () {
    $user = factory(User::class)->create();

    expect($user->setUpdatedAtAttribute('foo'))->toBeEmpty();
    expect($user->setUpdatedAt('foo'))->toBeEmpty();
});
