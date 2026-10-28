<?php

declare(strict_types=1);

namespace Pollora\Colt\Model;

/**
 * Tag class.
 *
 * @author Mickael Burguet <www.rundef.com>
 */
class Tag extends Taxonomy
{
    /**
     * @var string
     */
    protected $taxonomy = 'post_tag';
}
