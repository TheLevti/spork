<?php

/*
 * This file is part of the thelevti/phpfork package.
 *
 * (c) Petr Levtonov <petr@levtonov.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phpfork\Batch\Strategy;

use PHPUnit\Framework\TestCase;

class ChunkStrategyTest extends TestCase
{
    /**
     * @dataProvider provideNumber
     */
    public function testChunkArray($number, $expectedCounts)
    {
        $strategy = new ChunkStrategy($number);
        $batches = $strategy->createBatches(range(1, 100));

        $batchesCount = 0;
        foreach ($batches as $i => $batch) {
            ++$batchesCount;
            $this->assertCount($expectedCounts[$i], $batch);
        }

        $this->assertEquals(count($expectedCounts), $batchesCount);
    }

    public function provideNumber()
    {
        return [
            [1, [100]],
            [2, [50, 50]],
            [3, [34, 34, 32]],
            [4, [25, 25, 25, 25]],
            [5, [20, 20, 20, 20, 20]],
        ];
    }
}