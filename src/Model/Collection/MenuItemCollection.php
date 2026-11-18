<?php

declare(strict_types=1);

namespace Pollora\Colt\Model\Collection;

use Pollora\Colt\Model\MenuItem;

/**
 * Collection of menu items that resolves the target (`instance()`) and the
 * parent (`parent()`) of all its items with one query per target class.
 *
 * Without it, each item runs its own `find()` (plus the meta of the found
 * model): 2 queries per item and per call. The batch is lazy: nothing is
 * queried until the first item asks for its instance or parent, so code that
 * never calls them pays nothing.
 *
 * @template TKey of array-key
 * @template TModel of MenuItem
 *
 * @extends PostCollection<TKey, TModel>
 */
class MenuItemCollection extends PostCollection
{
    /**
     * Resolves, for every item not resolved yet, the model that its meta key points to.
     *
     * One `whereIn` query per target class; the result is stored on each item.
     *
     * @param  string  $metaKey  `_menu_item_object_id` (instance) or `_menu_item_menu_item_parent` (parent)
     *
     * @internal called by MenuItem
     */
    public function resolveTargets(string $metaKey): void
    {
        /** @var array<class-string, array<string, list<MenuItem>>> $pending class => id => items */
        $pending = [];

        foreach ($this->items as $item) {
            if (! $item instanceof MenuItem || $item->hasResolvedTarget($metaKey)) {
                continue;
            }

            $reference = $item->targetReference($metaKey);

            if ($reference === null) {
                $item->storeResolvedTarget($metaKey, null);

                continue;
            }

            $pending[$reference[0]][(string) $reference[1]][] = $item;
        }

        foreach ($pending as $class => $byId) {
            $found = (new $class)->newQuery()->findMany(array_keys($byId))
                ->keyBy(fn ($candidate): string => (string) $candidate->getKey());

            foreach ($byId as $id => $items) {
                $model = $found->get((string) $id);

                foreach ($items as $item) {
                    $item->storeResolvedTarget($metaKey, $model);
                }
            }
        }
    }
}
