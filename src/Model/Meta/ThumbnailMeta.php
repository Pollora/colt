<?php

declare(strict_types=1);

namespace Pollora\Colt\Model\Meta;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Pollora\Colt\Model\Attachment;

/**
 * Class ThumbnailMeta
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 *
 * @property-read Attachment|null $attachment
 */
class ThumbnailMeta extends PostMeta
{
    const SIZE_THUMBNAIL = 'thumbnail';

    const SIZE_MEDIUM = 'medium';

    const SIZE_LARGE = 'large';

    const SIZE_FULL = 'full';

    /**
     * @var array
     */
    protected $with = ['attachment'];

    /**
     * @return BelongsTo
     */
    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'meta_value');
    }

    /**
     * @param  string  $size
     * @return array
     *
     * @throws \Exception
     */
    public function size($size)
    {
        if ($size == self::SIZE_FULL) {
            return $this->attachment->url;
        }

        $meta = $this->maybeUnserialize($this->attachment->meta->_wp_attachment_metadata);
        $sizes = Arr::get($meta, 'sizes');

        if (! isset($sizes[$size])) {
            return $this->attachment->url;
        }

        $data = Arr::get($sizes, $size);

        return array_merge($data, [
            'url' => dirname($this->attachment->url).'/'.$data['file'],
        ]);
    }

    public function __toString(): string
    {
        return (string) $this->attachment->guid;
    }
}
