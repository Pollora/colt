<?php

declare(strict_types=1);

/*
 * Contracts of the model helpers around the batching: which collection a
 * model keeps, that the meta of a term is refreshed after a bulk save, that
 * a relation read hands its connection to the related models, and that the
 * custom timestamps chain.
 */

use Illuminate\Database\Eloquent\Collection;
use Pollora\Colt\Model\Comment;
use Pollora\Colt\Model\Post;
use Pollora\Colt\Model\Term;
use Pollora\Colt\Tests\Support\ColtFixtures;

it('keeps the first collection of a term to load its meta, not the filtered copies', function (): void {
    ColtFixtures::posts(5);
    $all = Term::all();
    $all->take(2);

    $all->first()->meta;

    expect($all->map(fn (Term $term): bool => $term->relationLoaded('meta'))->all())
        ->toBe(array_fill(0, $all->count(), true));
});

it('hands the connection of a model to the related models it reads from a collection relation', function (): void {
    $post = new Post;
    $post->setConnection('foo');
    $comment = new Comment;
    $comment->setConnection('wp');
    $post->setRelation('comments', new Collection([$comment]));

    expect($post->comments->first()->getConnectionName())->toBe('foo');
});

it('hands the connection of a model to the single related model it reads', function (): void {
    $post = new Post;
    $post->setConnection('foo');
    $author = new Post;
    $author->setConnection('wp');
    $post->setRelation('parent', $author);

    expect($post->parent->getConnectionName())->toBe('foo');
});

it('refreshes the meta of a post after saving several meta at once', function (): void {
    [$id] = ColtFixtures::posts(1);
    $post = Post::find($id);
    expect($post->meta->extra)->toBeNull();

    $post->saveMeta(['extra' => 'value']);

    expect($post->meta->extra)->toBe('value');
});

it('chains the custom timestamp setters and mirrors them in GMT', function (): void {
    $post = new Post;

    expect($post->setUpdatedAt('2026-02-03 04:05:06'))->toBe($post)
        ->and($post->setCreatedAt('2026-01-02 03:04:05'))->toBe($post)
        ->and($post->post_modified_gmt)->toBe('2026-02-03 04:05:06')
        ->and($post->post_date_gmt)->toBe('2026-01-02 03:04:05');
});
