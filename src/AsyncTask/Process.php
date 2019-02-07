<?php
/**
 * AsyncTask.
 *
 * Copyright (c) 2019, Dmitry Mamontov <d.slonyara@gmail.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Dmitry Mamontov nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2019 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 *
 * @since     File available since Release 2.0.13
 */

namespace AsyncTask;

use AsyncTask\Exception\ProcessException;

/**
 * The class allows you to manage child processes.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2019 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 *
 * @version   Release: 2.0.13
 *
 * @see      https://github.com/dmamontov/asynctask
 * @since     Class available since Release 2.0.13
 */
class Process
{
    /**
     * Enables asynchronous signal processing.
     */
    public function __construct()
    {
        if (!pcntl_async_signals()) {
            pcntl_async_signals(true);
        }
    }

    /**
     * Sets the process header.
     *
     * @param string $title
     *
     * @throws ProcessException
     */
    public function setTitle(string $title): void
    {
        if (PHP_SAPI !== 'cli') {
            throw new ProcessException('Setting the process header is only possible in cli mode.');
        }

        if (!cli_set_process_title($title)) {
            throw new ProcessException(sprintf('Could not set process header for PID %i.', getmypid()));
        }
    }

    /**
     * Detach the child process from the parent.
     *
     * @throws ProcessException
     */
    public function untie(): void
    {
        if (false === pcntl_signal(SIGCHLD, SIG_IGN)) {
            throw new ProcessException('Failed to set a signal handler.');
        }
    }

    /**
     * Creates a child process from the parent.
     *
     * @return bool
     */
    public function fork(): bool
    {
        $pid = pcntl_fork();
        if (-1 == $pid) {
            exit();
        }

        return !$pid;
    }

    /**
     * Waiting for the completion of the child process.
     *
     * @param int $pid
     *
     * @return bool
     */
    public function wait(int $pid): bool
    {
        return -1 != pcntl_waitpid($pid, $status, WNOHANG) || 0 == $pid;
    }

    /**
     * Kills child process.
     *
     * @param int $pid
     */
    public function kill(int $pid): void
    {
        PidSystem::remove($pid, PidSystem::TYPE_PID);
        posix_kill($pid, SIGKILL);
    }

    /**
     * Kills all processes associated with the current class.
     */
    public static function killAll(): void
    {
        foreach (PidSystem::getOpenPids(PidSystem::FILTER_ALL) as $pid) {
            posix_kill($pid, SIGKILL);
        }
    }
}
