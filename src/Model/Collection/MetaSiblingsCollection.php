<?php

declare(strict_types=1);

namespace Pollora\Colt\Model\Collection;

use Illuminate\Database\Eloquent\Collection;
use Pollora\Colt\Model;

/**
 * Collection of models with meta (terms, comments) that loads the `meta`
 * relation for all its models at once when the first one reads it.
 *
 * Posts and users always load their meta with the query (`$with`); terms and
 * comments do not, so reading `$term->meta` in a loop ran one query per term.
 * Nothing is queried until a model reads its meta, so code that does not read
 * it pays nothing, and the meta is not added to the serialised models.
 *
 * @template TKey of array-key
 * @template TModel of Model
 *
 * @extends Collection<TKey, TModel>
 */
class MetaSiblingsCollection extends Collection
{
    /**
     * @param  iterable<TKey, TModel>|null  $items
     */
    public function __construct($items = [])
    {
        parent::__construct($items);

        foreach ($this->items as $model) {
            if ($model instanceof Model) {
                $model->joinMetaSiblings($this);
            }
        }
    }
}
