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

namespace Phpfork\Deferred;

interface PromiseInterface
{
    public const STATE_PENDING = 'pending';
    public const STATE_RESOLVED = 'resolved';
    public const STATE_REJECTED = 'rejected';

    /**
     * Returns the promise state.
     *
     *  * PromiseInterface::STATE_PENDING:  The promise is still open
     *  * PromiseInterface::STATE_RESOLVED: The promise completed successfully
     *  * PromiseInterface::STATE_REJECTED: The promise failed
     *
     * @return string A promise state constant
     */
    public function getState();

    /**
     * Adds a callback to be called upon progress.
     *
     * @param callable $progress The callback
     *
     * @return PromiseInterface The current promise
     */
    public function progress($progress);

    /**
     * Adds a callback to be called whether the promise is resolved or rejected.
     *
     * The callback will be called immediately if the promise is no longer
     * pending.
     *
     * @param callable $always The callback
     *
     * @return PromiseInterface The current promise
     */
    public function always($always);

    /**
     * Adds a callback to be called when the promise completes successfully.
     *
     * The callback will be called immediately if the promise state is resolved.
     *
     * @param callable $done The callback
     *
     * @return PromiseInterface The current promise
     */
    public function done($done);

    /**
     * Adds a callback to be called when the promise fails.
     *
     * The callback will be called immediately if the promise state is rejected.
     *
     * @param callable $fail The callback
     *
     * @return PromiseInterface The current promise
     */
    public function fail($fail);

    /**
     * Adds done and fail callbacks.
     *
     * @param callable $done The done callback
     * @param callable $fail The fail callback
     *
     * @return PromiseInterface The current promise
     */
    public function then($done, $fail = null);
}