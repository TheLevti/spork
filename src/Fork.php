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

namespace Phpfork;

use Phpfork\Deferred\Deferred;
use Phpfork\Deferred\DeferredInterface;
use Phpfork\Exception\ForkException;
use Phpfork\Exception\ProcessControlException;
use Phpfork\Util\ExitMessage;

class Fork implements DeferredInterface
{
    private $defer;
    private $pid;
    private $shm;
    private $debug;
    private $name;
    private $status;
    private $message;

    public function __construct($pid, SharedMemory $shm, $debug = false)
    {
        $this->defer = new Deferred();
        $this->pid = $pid;
        $this->shm = $shm;
        $this->debug = $debug;
        $this->name = '<anonymous>';
    }

    /**
     * Assign a name to the current fork (useful for debugging).
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function wait($hang = true)
    {
        if ($this->isExited()) {
            return $this;
        }

        if (-1 === $pid = pcntl_waitpid($this->pid, $status, ($hang ? 0 : WNOHANG) | WUNTRACED)) {
            throw new ProcessControlException('Error while waiting for process ' . $this->pid);
        }

        if ($this->pid === $pid) {
            $this->processWaitStatus($status);
        }

        return $this;
    }

    /**
     * Processes a status value retrieved while waiting for this fork to exit.
     */
    public function processWaitStatus($status)
    {
        if ($this->isExited()) {
            throw new \LogicException('Cannot set status on an exited fork');
        }

        $this->status = $status;

        if ($this->isExited()) {
            $this->receive();

            $this->shm->cleanup();

            $this->isSuccessful() ? $this->resolve() : $this->reject();

            if ($this->debug && (!$this->isSuccessful() || $this->getError())) {
                throw new ForkException($this->name, $this->pid, $this->getError());
            }
        }
    }

    public function receive()
    {
        $messages = [];

        foreach ($this->shm->receive() as $message) {
            if ($message instanceof ExitMessage) {
                $this->message = $message;
            } else {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    public function kill($signal = SIGINT)
    {
        if (false === $this->shm->signal($signal)) {
            throw new ProcessControlException('Unable to send signal');
        }

        return $this;
    }

    public function getResult()
    {
        if ($this->message) {
            return $this->message->getResult();
        }
    }

    public function getOutput()
    {
        if ($this->message) {
            return $this->message->getOutput();
        }
    }

    public function getError()
    {
        if ($this->message) {
            return $this->message->getError();
        }
    }

    public function isSuccessful()
    {
        return 0 === $this->getExitStatus();
    }

    public function isExited()
    {
        return null !== $this->status && pcntl_wifexited($this->status);
    }

    public function isStopped()
    {
        return null !== $this->status && pcntl_wifstopped($this->status);
    }

    public function isSignaled()
    {
        return null !== $this->status && pcntl_wifsignaled($this->status);
    }

    public function getExitStatus()
    {
        if (null !== $this->status) {
            return pcntl_wexitstatus($this->status);
        }
    }

    public function getTermSignal()
    {
        if (null !== $this->status) {
            return pcntl_wtermsig($this->status);
        }
    }

    public function getStopSignal()
    {
        if (null !== $this->status) {
            return pcntl_wstopsig($this->status);
        }
    }

    public function getState()
    {
        return $this->defer->getState();
    }

    public function progress($progress): Fork
    {
        $this->defer->progress($progress);

        return $this;
    }

    public function always($always): Fork
    {
        $this->defer->always($always);

        return $this;
    }

    public function done($done): Fork
    {
        $this->defer->done($done);

        return $this;
    }

    public function fail($fail): Fork
    {
        $this->defer->fail($fail);

        return $this;
    }

    public function then($done, $fail = null): Fork
    {
        $this->defer->then($done, $fail);

        return $this;
    }

    public function notify(): Fork
    {
        $args = func_get_args();
        array_unshift($args, $this);

        call_user_func_array([$this->defer, 'notify'], array_values($args));

        return $this;
    }

    public function resolve(): Fork
    {
        $args = func_get_args();
        array_unshift($args, $this);

        call_user_func_array([$this->defer, 'resolve'], array_values($args));

        return $this;
    }

    public function reject(): Fork
    {
        $args = func_get_args();
        array_unshift($args, $this);

        call_user_func_array([$this->defer, 'reject'], array_values($args));

        return $this;
    }
}