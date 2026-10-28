<?php

declare(strict_types=1);

namespace Pollora\Colt\Tests\Unit\Model;

use Pollora\Colt\Model\Post;

class Video extends Post
{
    protected $postType = 'video';
}
