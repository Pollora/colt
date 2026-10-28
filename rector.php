<?php

declare(strict_types=1);

use Pollora\Harness\Profile;
use Pollora\Harness\Rector\Preset;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamFromDimFetchKeyUseRector;

return Preset::configure(__DIR__, Profile::Integration)
    ->withSkip([
        // Each skip says why, like the framework's rector.php.
        // Narrows the $term parameter of the public hasTerm() to string from the way it indexes an array; a caller may pass an integer (a term id).
        AddParamFromDimFetchKeyUseRector::class,
    ]);
