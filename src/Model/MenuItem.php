<?php

declare(strict_types=1);

namespace Pollora\Colt\Model;

use Illuminate\Support\Arr;
use Pollora\Colt\Model\Collection\MenuItemCollection;

/**
 * Class MenuItem
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class MenuItem extends Post
{
    /**
     * @var string
     */
    protected $postType = 'nav_menu_item';

    /**
     * @var array
     */
    protected $instanceRelations = [
        'post' => Post::class,
        'page' => Page::class,
        'custom' => CustomLink::class,
        'category' => Taxonomy::class,
    ];

    /**
     * Targets resolved by the siblings batch and not read yet, by meta key.
     * Read once, then dropped: a later call queries again, as it always did.
     *
     * @var array<string, Post|Page|CustomLink|Taxonomy|null>
     */
    private array $resolvedTargets = [];

    /**
     * Meta keys whose batched target was already read: the item is no longer
     * part of a batch for them, so a loop over the collection batches once.
     *
     * @var array<string, true>
     */
    private array $readTargets = [];

    /**
     * @return Post|Page|CustomLink|Taxonomy|null the parent menu entry's target
     */
    public function parent()
    {
        return $this->resolveTarget('_menu_item_menu_item_parent');
    }

    /**
     * @return Post|Page|CustomLink|Taxonomy|null the post, page, link or term this item points to
     */
    public function instance()
    {
        return $this->resolveTarget('_menu_item_object_id');
    }

    /**
     * Items come back in a collection that batches `instance()` and `parent()`.
     *
     * @param  array<int, static>  $models
     */
    public function newCollection(array $models = []): MenuItemCollection
    {
        return new MenuItemCollection($models);
    }

    /**
     * @return array{0: class-string, 1: mixed}|null target class and id, null when there is nothing to look up
     *
     * @internal called by MenuItemCollection
     */
    public function targetReference(string $metaKey): ?array
    {
        $className = $this->getClassName();
        $id = $this->meta->$metaKey;

        if (! $className || $id === null || $id === '') {
            return null;
        }

        return [$className, $id];
    }

    /**
     * @internal called by MenuItemCollection
     */
    public function hasResolvedTarget(string $metaKey): bool
    {
        return array_key_exists($metaKey, $this->resolvedTargets) || isset($this->readTargets[$metaKey]);
    }

    /**
     * @param  Post|Page|CustomLink|Taxonomy|null  $target
     *
     * @internal called by MenuItemCollection
     */
    public function storeResolvedTarget(string $metaKey, $target): void
    {
        $this->resolvedTargets[$metaKey] = $target;
    }

    /**
     * @return string|null class of the target, from the item's object type
     */
    protected function getClassName()
    {
        return Arr::get(
            $this->instanceRelations,
            $this->meta->_menu_item_object
        );
    }

    /**
     * Finds the model that the meta key points to: from the siblings batch when
     * the item belongs to a collection, with its own query otherwise.
     *
     * @return Post|Page|CustomLink|Taxonomy|null
     */
    private function resolveTarget(string $metaKey)
    {
        if ($this->siblings instanceof MenuItemCollection) {
            $this->siblings->resolveTargets($metaKey);

            if (array_key_exists($metaKey, $this->resolvedTargets)) {
                $target = $this->resolvedTargets[$metaKey];
                unset($this->resolvedTargets[$metaKey]);
                $this->readTargets[$metaKey] = true;

                return $target;
            }
        }

        if ($className = $this->getClassName()) {
            return (new $className)->newQuery()->find($this->meta->$metaKey);
        }

        return null;
    }
}
