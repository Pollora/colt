<?php

use Thunder\Shortcode\Parser\RegularParser;

test('it has all necessary keys', function () {
    $file = __DIR__ . '/../../../src/Laravel/config.php';
    $content = require $file;

    // Database connection
    expect($content)->toHaveKey('connection');
    expect($content['connection'])->toEqual('colt');

    // Post types
    expect($content)->toHaveKey('post_types');
    expect($content['post_types'])->toBeEmpty();

    // Shortcodes
    expect($content)->toHaveKey('shortcodes');
    expect($content['shortcodes'])->toBeEmpty();

    // Shortcode parser
    expect($content)->toHaveKey('shortcode_parser');
    expect($content['shortcode_parser'])->toEqual(RegularParser::class);
});
