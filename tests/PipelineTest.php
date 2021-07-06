<?php

/**
 * This file is part of data-pipe, a Matchory application.
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
use Matchory\DataPipe\Pipeline;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Throwable;

class PipelineTest extends TestCase
{
    /**
     * @throws Throwable
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @covers \Matchory\DataPipe\Pipeline
     */
    public function testProcess(): void
    {
        $pipeline = new Pipeline([], new EventDispatcher());
        $payload = new Payload();
        $result = $pipeline->process($payload);

        $this->assertInstanceOf(Payload::class, $result);
    }
}
