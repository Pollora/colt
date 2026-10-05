<?php

use Pollora\Colt\Tests\TestCase;
use Pollora\Colt\Tests\WordPress\TestCase as WordPressTestCase;

/*
|--------------------------------------------------------------------------
| Test Cases
|--------------------------------------------------------------------------
|
| Unit tests run against in-memory SQLite databases. The WordPress suite runs
| against a real WordPress installation (composer test:wordpress).
|
*/

pest()->extend(TestCase::class)->in('Unit');

pest()->extend(WordPressTestCase::class)->in('WordPress');
