<?php

namespace Pollora\Colt\Concerns;

use Illuminate\Database\Eloquent\Builder;
use UnexpectedValueException;

/**
 * Trait HasMetaFields
 *
 * @package Pollora\Colt\Traits
 * @author Junior Grossi <juniorgro@gmail.com>
 */
trait MetaFields
{
    /**
     * @var array
     */
    protected $builtInClasses = [
        \Pollora\Colt\Model\Comment::class => \Pollora\Colt\Model\Meta\CommentMeta::class,
        \Pollora\Colt\Model\Post::class => \Pollora\Colt\Model\Meta\PostMeta::class,
        \Pollora\Colt\Model\Term::class => \Pollora\Colt\Model\Meta\TermMeta::class,
        \Pollora\Colt\Model\User::class => \Pollora\Colt\Model\Meta\UserMeta::class,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fields()
    {
        return $this->meta();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function meta()
    {
        return $this->hasMany($this->getMetaClass(), $this->getMetaForeignKey());
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    protected function getMetaClass()
    {
        foreach ($this->builtInClasses as $model => $meta) {
            if ($this instanceof $model) {
                return $meta;
            }
        }

        throw new UnexpectedValueException(sprintf(
            '%s must extends one of Colt built-in models: Comment, Post, Term or User.',
            static::class
        ));
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    protected function getMetaForeignKey(): string
    {
        foreach ($this->builtInClasses as $model => $meta) {
            if ($this instanceof $model) {
                return sprintf('%s_id', strtolower(class_basename($model)));
            }
        }

        throw new UnexpectedValueException(sprintf(
            '%s must extends one of Colt built-in models: Comment, Post, Term or User.',
            static::class
        ));
    }

    /**
     * @param Builder $query
     * @param string|array $meta
     * @param mixed $value
     * @param string $operator
     * @return Builder
     */
    public function scopeHasMeta(Builder $query, $meta, $value = null, string $operator = '=')
    {
        if (!is_array($meta)) {
            $meta = [$meta => $value];
        }

        foreach ($meta as $key => $value) {
            $query->whereHas('meta', function (Builder $query) use ($key, $value, $operator) {
                if (!is_string($key)) {
                    return $query->where('meta_key', $operator, $value);
                }
                $query->where('meta_key', $operator, $key);

                return is_null($value) ? $query :
                    $query->where('meta_value', $operator, $value);
            });
        }

        return $query;
    }

    /**
     * @param Builder $query
     * @param string $meta
     * @param mixed $value
     * @return Builder
     */
    public function scopeHasMetaLike(Builder $query, $meta, $value = null)
    {
        return $this->scopeHasMeta($query, $meta, $value, 'like');
    }


    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function saveField($key, $value)
    {
        return $this->saveMeta($key, $value);
    }

    /**
     * @param string|array $key
     * @param mixed $value
     * @return bool
     */
    public function saveMeta($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->saveOneMeta($k, $v);
            }
            $this->load('meta');

            return true;
        }

        return $this->saveOneMeta($key, $value);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    private function saveOneMeta($key, $value)
    {
        if ($this->usesWordPressMetaApi()) {
            $type = $this->getMetaType();
            $result = update_metadata($type, $this->getKey(), wp_slash($key), wp_slash($value));
            $this->load('meta');

            // update_metadata() also returns false when the stored value is already the same.
            return $result !== false || get_metadata($type, $this->getKey(), $key, true) == $value;
        }

        $meta = $this->meta()->where('meta_key', $key)
            ->firstOrNew(['meta_key' => $key]);

        $result = $meta->fill(['meta_value' => $value])->save();
        $this->load('meta');

        return $result;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createField($key, $value)
    {
        return $this->createMeta($key, $value);
    }

    /**
     * @param string|array $key
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection
     */
    public function createMeta($key, $value = null)
    {
        if (is_array($key)) {
            return collect($key)->map(function ($value, $key) {
                return $this->createOneMeta($key, $value);
            });
        }

        return $this->createOneMeta($key, $value);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private function createOneMeta($key, $value)
    {
        if ($this->usesWordPressMetaApi()) {
            $metaId = add_metadata($this->getMetaType(), $this->getKey(), wp_slash($key), wp_slash($value));
            $this->load('meta');

            return $metaId === false ? null : $this->meta->firstWhere('meta_id', $metaId);
        }

        $meta =  $this->meta()->create([
            'meta_key' => $key,
            'meta_value' => $value,
        ]);
        $this->load('meta');

        return $meta;
    }

    /**
     * Writes go through WordPress's meta API when it is loaded, so that
     * sanitize callbacks, meta hooks and the object cache all apply.
     *
     * @return bool
     */
    protected function usesWordPressMetaApi(): bool
    {
        return function_exists('update_metadata');
    }

    /**
     * @return string post, term, user or comment, as WordPress's meta API names them
     * @throws \UnexpectedValueException
     */
    protected function getMetaType(): string
    {
        return substr($this->getMetaForeignKey(), 0, -3);
    }

    /**
     * @param string $attribute
     * @return mixed|null
     */
    public function getMeta($attribute)
    {
        if ($meta = $this->meta->{$attribute}) {
            return $meta;
        }

        return null;
    }
}
