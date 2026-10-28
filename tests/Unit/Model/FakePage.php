<?php

declare(strict_types=1);

namespace Pollora\Colt\Tests\Unit\Model;

use Pollora\Colt\Model\Post;

class FakePage extends Post
{
    protected $postType = 'fake_page';
}
