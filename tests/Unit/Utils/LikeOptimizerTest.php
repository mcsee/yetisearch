<?php

declare(strict_types=1);

namespace YetiSearch\Tests\Unit\Utils;

use PHPUnit\Framework\TestCase;
use YetiSearch\Utils\LikeOptimizer;

final class LikeOptimizerTest extends TestCase
{
    public function testGivenEmptyArrayWhenOptimizeThenReturnEmptyArray(): void
    {
        $optimizer = new LikeOptimizer();
        $result = $optimizer->optimize([]);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGivenSingleElementArrayWhenOptimizeThenReturnSameElement(): void
    {
        $optimizer = new LikeOptimizer();
        $input = ['neron'];
        $result = $optimizer->optimize($input);
        $this->assertEquals($input, $result);
    }
}
