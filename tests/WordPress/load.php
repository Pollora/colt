<?php

/**
 * Loads WordPress for the integration suite, through PHP's auto_prepend_file:
 *
 *     php -d auto_prepend_file=tests/WordPress/load.php vendor/bin/pest -c phpunit.wordpress.xml
 *
 * WordPress has to come first and at global scope: it and Laravel both declare
 * __(), and Laravel only skips its own when WordPress's already exists, while
 * vendor/bin/phpunit loads Composer's autoloader before any PHPUnit bootstrap.
 */

$wordpressPath = getenv('WP_PATH') ?: dirname(__DIR__, 2) . '/.wordpress';

if (!is_file($wordpressPath . '/wp-load.php')) {
    fwrite(STDERR, "WordPress not found in {$wordpressPath}: install it there with WP-CLI (see the wordpress job of .github/workflows/ci.yml) or set WP_PATH.\n");
    exit(1);
}

define('WP_USE_THEMES', false);

require $wordpressPath . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

unset($wordpressPath);
