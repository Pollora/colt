<?php

declare(strict_types=1);

use Pollora\Colt\Model\Meta\TermMeta;
use Pollora\Colt\Model\Term;

test('term relation', function () {
    $term_meta = factory(TermMeta::class)->create();

    expect($term_meta->term)->toBeInstanceOf(Term::class);
});
