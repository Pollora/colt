<?php

namespace Pollora\Colt\Tests\Unit\Laravel;

use Pollora\Colt\Tests\TestCase;
use Thunder\Shortcode\Parser\RegularParser;

/**
 * Class LaravelConfigTest
 *
 * @package Pollora\Colt\Tests\Unit\Laravel
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class LaravelConfigTest extends TestCase
{
    public function test_it_has_all_necessary_keys()
    {
        $file = __DIR__ . '/../../../src/Laravel/config.php';
        $content = require $file;

        // Database connection
        $this->assertArrayHasKey('connection', $content);
        $this->assertEquals('colt', $content['connection']);

        // Post types
        $this->assertArrayHasKey('post_types', $content);
        $this->assertEmpty($content['post_types']);

        // Shortcodes
        $this->assertArrayHasKey('shortcodes', $content);
        $this->assertEmpty($content['shortcodes']);

        // Shortcode parser
        $this->assertArrayHasKey('shortcode_parser', $content);
        $this->assertEquals(RegularParser::class, $content['shortcode_parser']);
    }
}
