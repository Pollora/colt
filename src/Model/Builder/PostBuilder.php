<?php

declare(strict_types=1);

namespace Pollora\Colt\Model\Builder;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class PostBuilder
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class PostBuilder extends Builder
{
    /**
     * @param  string  $status
     * @return PostBuilder
     */
    public function status($status)
    {
        return $this->where('post_status', $status);
    }

    /**
     * @return PostBuilder
     */
    public function published()
    {
        return $this->where(function ($query): void {
            $query->status('publish');
            $query->orWhere(function ($query): void {
                $query->status('future');
                $query->where('post_date', '<=', Carbon::now()->format('Y-m-d H:i:s'));
            });
        });
    }

    /**
     * @param  string  $type
     * @return PostBuilder
     */
    public function type($type)
    {
        return $this->where('post_type', $type);
    }

    /**
     * @return PostBuilder
     */
    public function typeIn(array $types)
    {
        return $this->whereIn('post_type', $types);
    }

    /**
     * @param  string  $slug
     * @return PostBuilder
     */
    public function slug($slug)
    {
        return $this->where('post_name', $slug);
    }

    /**
     * @param  string  $postParentId
     * @return PostBuilder
     */
    public function parent($postParentId)
    {
        return $this->where('post_parent', $postParentId);
    }

    /**
     * @param  string  $taxonomy
     * @param  mixed  $terms
     * @return PostBuilder
     */
    public function taxonomy($taxonomy, $terms)
    {
        return $this->whereHas('taxonomies', function ($query) use ($taxonomy, $terms): void {
            $query->where('taxonomy', $taxonomy)
                ->whereHas('term', function ($query) use ($terms): void {
                    $query->whereIn('slug', is_array($terms) ? $terms : [$terms]);
                });
        });
    }

    /**
     * @param  mixed  $term
     * @return PostBuilder
     */
    public function search($term = false)
    {
        if (empty($term)) {
            return $this;
        }

        $terms = is_string($term) ? explode(' ', $term) : $term;

        $terms = collect($terms)->map(fn ($term): string => trim(str_replace('%', '', $term)))->filter()->map(fn ($term): string => '%'.$term.'%');

        if ($terms->isEmpty()) {
            return $this;
        }

        return $this->where(function ($query) use ($terms): void {
            $terms->each(function ($term) use ($query): void {
                $query->orWhere('post_title', 'like', $term)
                    ->orWhere('post_excerpt', 'like', $term)
                    ->orWhere('post_content', 'like', $term);
            });
        });
    }
}
