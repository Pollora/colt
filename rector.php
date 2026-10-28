<?php

declare(strict_types=1);

use Pollora\Harness\Profile;
use Pollora\Harness\Rector\Preset;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return Preset::configure(__DIR__, Profile::Integration)
    ->withSkip([
        // Each skip says why, like the framework's rector.php.
        // A void return type on a method that overrides an Eloquent one (setUpdatedAt() returns $this there) or that a user's model overrides breaks the subclass.
        AddVoidReturnTypeWhereNoReturnRector::class,
    ]);
