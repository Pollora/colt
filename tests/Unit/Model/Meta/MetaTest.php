<?php

namespace Pollora\Colt\Tests\Unit\Model\Meta;

use Pollora\Colt\Model\Meta\PostMeta;
use Pollora\Colt\Tests\TestCase;

class MetaTest extends TestCase
{
    public function test_it_unserialize_serialized_values()
    {
        $meta = factory(PostMeta::class)->create(['meta_value' => serialize('foo')]);
        $this->assertEquals('foo', $meta->value);
    }

    public function test_it_also_works_with_unserialized_values()
    {
        $meta = factory(PostMeta::class)->create(['meta_value' => 'foo']);
        $this->assertEquals('foo', $meta->value);
    }
}
