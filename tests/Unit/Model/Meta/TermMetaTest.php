<?php

namespace Pollora\Colt\Tests\Unit\Model\Meta;

use Pollora\Colt\Model\Meta\PostMeta;
use Pollora\Colt\Model\Meta\TermMeta;
use Pollora\Colt\Model\Post;
use Pollora\Colt\Model\Term;

/**
 * Class TermMetaTest
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class TermMetaTest extends \Pollora\Colt\Tests\TestCase
{
    public function test_term_relation()
    {
        $term_meta = factory(TermMeta::class)->create();

        $this->assertInstanceOf(Term::class, $term_meta->term);
    }
}
