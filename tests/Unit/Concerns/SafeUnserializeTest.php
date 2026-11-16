<?php

declare(strict_types=1);

use Pollora\Colt\Concerns\SafeUnserialize;

function safeUnserializer(): object
{
    return new class
    {
        use SafeUnserialize;

        public function run(mixed $value): mixed
        {
            return $this->maybeUnserialize($value);
        }
    };
}

it('returns non string values untouched', function () {
    expect(safeUnserializer()->run(42))->toBe(42)
        ->and(safeUnserializer()->run(['a']))->toBe(['a'])
        ->and(safeUnserializer()->run(null))->toBeNull();
});

it('unserializes arrays', function () {
    expect(safeUnserializer()->run(serialize(['a' => 1])))->toBe(['a' => 1]);
});

it('unserializes a serialized false as false', function () {
    expect(safeUnserializer()->run(serialize(false)))->toBeFalse();
});

it('returns plain strings that are not serialized as they are', function () {
    expect(safeUnserializer()->run('just text'))->toBe('just text');
});

it('never instantiates a serialized object', function () {
    $result = safeUnserializer()->run(serialize(new ArrayObject([1])));

    expect($result)->toBeInstanceOf(__PHP_Incomplete_Class::class);
});
