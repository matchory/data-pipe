<?php

/**
 * This file is part of data-processing-pipelines, a Matchory application.
 *
 * Unauthorized copying of this file, via any medium, is strictly prohibited.
 * Its contents are strictly confidential and proprietary.
 *
 * @copyright 2020–2021 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\DataPipe\Tests;

use Matchory\DataPipe\Payload\Payload;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function array_keys;
use function array_values;

class PayloadTest extends TestCase
{
    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testGetAttributeValues(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertSame(
            array_values($attributes),
            $payload->getAttributeValues()
        );
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testToArray(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertSame(
            $payload->getAttributes(),
            $payload->toArray()
        );
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testGetAttribute(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertSame(
            $attributes['a'],
            $payload->getAttribute('a')
        );
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testGetAttributeReturnsNullForMissingAttributes(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertNull($payload->getAttribute('c'));
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testSetAttribute(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertSame(10, $payload->getAttribute('a'));
        $payload->setAttribute('a', 20);
        self::assertSame(20, $payload->getAttribute('a'));
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testGetOriginalAttribute(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertSame(
            10,
            $payload->getOriginalAttribute('a')
        );
        self::assertSame(10, $payload->getAttribute('a'));
        $payload->setAttribute('a', 20);
        self::assertSame(20, $payload->getAttribute('a'));
        self::assertSame(
            10,
            $payload->getOriginalAttribute('a')
        );
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers       \Matchory\DataPipe\Payload\Payload
     * @noinspection PhpUndefinedFieldInspection
     */
    public function testMagicProperties(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertTrue(isset($payload->a));
        self::assertFalse(isset($payload->c));

        self::assertSame($attributes['a'], $payload->a);

        $payload->c = 40;

        self::assertSame(40, $payload->c);
        self::assertTrue(isset($payload->c));
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testWasChanged(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertFalse($payload->wasChanged('a'));
        $payload->setAttribute('a', 20);
        self::assertTrue($payload->wasChanged('a'));
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testWasChangedReturnsTrueForTypeChanges(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertFalse($payload->wasChanged('a'));
        $payload->setAttribute('a', '10');
        self::assertTrue($payload->wasChanged('a'));
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testGetChangedAttributes(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertEmpty($payload->getChangedAttributes());
        $payload->setAttribute('a', 20);
        $payload->setAttribute('c', 40);
        self::assertSame(
            ['a', 'c'],
            $payload->getChangedAttributes()
        );
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testJsonSerialize(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertSame(
            $attributes,
            $payload->jsonSerialize()
        );
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testGetOriginalAttributes(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertSame(
            $attributes,
            $payload->getOriginalAttributes()
        );
        self::assertSame(
            $payload->getAttributes(),
            $payload->getOriginalAttributes()
        );

        $payload->setAttribute('c', 30);

        self::assertNotSame(
            $payload->getAttributes(),
            $payload->getOriginalAttributes()
        );
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function test__constructWithAttributes(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertSame(
            $attributes,
            $payload->getAttributes()
        );
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function test__constructWithoutAttributes(): void
    {
        $payload = new Payload();

        self::assertSame(
            [],
            $payload->getAttributes()
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testGetAttributeNames(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
            'c' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertSame(
            array_keys($attributes),
            $payload->getAttributeNames()
        );
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testGetAttributes(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertSame(
            $attributes,
            $payload->getAttributes()
        );
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Payload\Payload
     */
    public function testHasAttribute(): void
    {
        $attributes = [
            'a' => 10,
            'b' => 20,
        ];
        $payload = new Payload($attributes);

        self::assertTrue($payload->hasAttribute('a'));
        self::assertTrue($payload->hasAttribute('b'));
        self::assertFalse($payload->hasAttribute('c'));
    }
}
