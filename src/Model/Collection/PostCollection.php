<?php

declare(strict_types=1);

namespace Pollora\Colt\Model\Collection;

use Illuminate\Database\Eloquent\Collection;
use Pollora\Colt\Model\Post;

/**
 * Collection of posts that serialises without one query per post.
 *
 * Serialising a post appends computed attributes (`image`, `terms`,
 * `main_category`, `keywords`, `keywords_str`) that read the `thumbnail` and
 * `taxonomies` relations. On a collection, each post would load them on its
 * own: 5 queries per post. Before serialising, the relations are loaded for
 * all the posts at once (one query per relation level), only for the
 * relations that the posts really append, and only the ones not loaded yet.
 *
 * The posts also remember the collection they come from, so that reading one
 * of those computed attributes on a single post in a loop loads the relation
 * for the whole collection once.
 *
 * @template TKey of array-key
 * @template TModel of Post
 *
 * @extends Collection<TKey, TModel>
 */
class PostCollection extends Collection
{
    /**
     * Appended attribute => relation it reads.
     *
     * @var array<string, string>
     */
    private const array APPEND_RELATIONS = [
        'image' => 'thumbnail',
        'terms' => 'taxonomies',
        'main_category' => 'taxonomies',
        'keywords' => 'taxonomies',
        'keywords_str' => 'taxonomies',
    ];

    /**
     * @param  iterable<TKey, TModel>|null  $items
     */
    public function __construct($items = [])
    {
        parent::__construct($items);

        foreach ($this->items as $post) {
            if ($post instanceof Post) {
                $post->joinSiblings($this);
            }
        }
    }

    /**
     * Loads a relation for every post of the collection that lacks it, in one query.
     *
     * Used by the computed attributes (`terms`, `image`...) so that reading
     * them in a loop costs one query for the whole collection, not one per post.
     *
     * @internal called by Post
     */
    public function loadRelationForAll(string $relation): void
    {
        $this->loadMissing($relation);
    }

    /**
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        $this->primeAppendedRelations();

        return parent::toArray();
    }

    /**
     * @return array<int, mixed>
     */
    public function jsonSerialize(): array
    {
        $this->primeAppendedRelations();

        return parent::jsonSerialize();
    }

    /**
     * Loads, for every post, the relations that its appended attributes read.
     */
    private function primeAppendedRelations(): void
    {
        $byRelation = [];

        foreach ($this->items as $post) {
            if (! $post instanceof Post) {
                continue;
            }

            foreach (array_unique(array_map(
                fn (string $append): ?string => self::APPEND_RELATIONS[$append] ?? null,
                $post->getAppends()
            )) as $relation) {
                if ($relation !== null && ! $post->relationLoaded($relation)) {
                    $byRelation[$relation][] = $post;
                }
            }
        }

        foreach ($byRelation as $relation => $posts) {
            (new Collection($posts))->load($relation);
        }
    }
}
