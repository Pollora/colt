<?php

namespace Pollora\Colt\Tests\Unit\Concerns;

use Pollora\Colt\Colt;
use Pollora\Colt\Model;
use Pollora\Colt\Model\Post;
use Pollora\Colt\Tests\TestCase;
use Thunder\Shortcode\Parser\ParserInterface;
use Thunder\Shortcode\Parser\WordpressParser;
use Thunder\Shortcode\ShortcodeFacade;

/**
 * Class ShortcodesTest
 *
 * @package Pollora\Colt\Tests\Unit\Concerns
 * @author Olivier Gorzalka <olivier@amphibee.fr>
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class ShortcodesTest extends TestCase
{
    public function test_it_can_change_in_the_config_file_if_laravel()
    {
        config(['colt.shortcode_parser' => WordpressParser::class]);

        $post = factory(Post::class)->create();
        $handler = $this->getHandler($post);
        $value = $this->getParserValue($handler);

        $this->assertInstanceOf(WordpressParser::class, $value);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_it_can_change_the_parser_in_runtime()
    {
        // Force Colt::isLaravel() returning false
        $mockedColt = \Mockery::mock('alias:' . Colt::class);
        $mockedColt->shouldReceive('isLaravel')->andReturn(false);

        /** @var Post $post */
        $post = factory(Post::class)->create();
        $post->setShortcodeParser(new WordpressParser());

        $handler = $this->getHandler($post);
        $value = $this->getParserValue($handler);

        $this->assertInstanceOf(WordpressParser::class, $value);
    }

    private function getHandler(Model $post): ShortcodeFacade
    {
        $method = new \ReflectionMethod($post, 'getShortcodeHandlerInstance');
        $method->setAccessible(true);

        return $method->invoke($post);
    }

    private function getParserValue(ShortcodeFacade $handler): ParserInterface
    {
        $property = new \ReflectionProperty($handler, 'parser');
        $property->setAccessible(true);

        return $property->getValue($handler);
    }
}
