<?php

use Illuminate\Container\Container;
use Pollora\Colt\Colt;
use Pollora\Colt\Model;
use Pollora\Colt\Model\Post;
use Thunder\Shortcode\Parser\ParserInterface;
use Thunder\Shortcode\Parser\WordpressParser;
use Thunder\Shortcode\ShortcodeFacade;

test('it can change in the config file if laravel', function () {
    config(['colt.shortcode_parser' => WordpressParser::class]);

    $post = factory(Post::class)->create();
    $handler = getHandler($post);
    $value = getParserValue($handler);

    expect($value)->toBeInstanceOf(WordpressParser::class);
});

test('it can change the parser in runtime', function () {
    /** @var Post $post */
    $post = factory(Post::class)->create();
    $post->setShortcodeParser(new WordpressParser());

    // Outside Laravel: app() returns a container that is not a Laravel application.
    Container::setInstance(new class extends Container {
        public function version(): string
        {
            return 'standalone';
        }
    });

    try {
        expect(Colt::isLaravel())->toBeFalse();

        $handler = getHandler($post);
    } finally {
        Container::setInstance($this->app);
    }

    expect(getParserValue($handler))->toBeInstanceOf(WordpressParser::class);
});

function getHandler(Model $post): ShortcodeFacade
{
    $method = new \ReflectionMethod($post, 'getShortcodeHandlerInstance');
    $method->setAccessible(true);

    return $method->invoke($post);
}

function getParserValue(ShortcodeFacade $handler): ParserInterface
{
    $property = new \ReflectionProperty($handler, 'parser');
    $property->setAccessible(true);

    return $property->getValue($handler);
}
