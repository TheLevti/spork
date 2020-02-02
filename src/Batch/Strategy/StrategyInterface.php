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

/**
 * @see BatchJob::__invoke()
 */
interface StrategyInterface
{
    /**
     * Creates an iterator for the supplied data.
     *
     * @param mixed $data The raw batch data
     *
     * @return array|\Traversable An iterator of batches
     */
    public function createBatches($data);

    /**
     * Creates a batch runner for the supplied list.
     *
     * A batch runner is a callable that is passed to ProcessManager::fork()
     * that should run each item in the supplied batch through a callable.
     *
     * @param mixed    $batch    A batch of items
     * @param callable $callback The batch callback
     *
     * @return callable A callable for the child process
     */
    public function createRunner($batch, $callback);
}