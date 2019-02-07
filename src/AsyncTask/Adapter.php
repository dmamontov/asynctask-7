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

use AsyncTask\Exception\AdapterException;
use AsyncTask\Interfaces\AdapterInterface;

/**
 * Abstract wrapper class with required functionality for other adapters.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2019 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 *
 * @version   Release: 2.0.13
 *
 * @see      https://github.com/dmamontov/asynctask
 * @since     Class available since Release 2.0.13
 * @abstract
 */
abstract class Adapter
{
    /**
     * @const integer STATUS_PENDING
     */
    const STATUS_PENDING = 0;

    /**
     * @const integer STATUS_RUNNING
     */
    const STATUS_RUNNING = 1;

    /**
     * @const integer STATUS_FINISHED
     */
    const STATUS_FINISHED = 2;

    /**
     * @const integer STATUS_CANCELED
     */
    const STATUS_CANCELED = 3;

    /**
     * @const integer STATUS_UNDEFINED
     */
    const STATUS_UNDEFINED = 4;

    /**
     * @const string STATUS_KEY
     */
    const STATUS_KEY = '1001';

    /**
     * @const string PID_KEY
     */
    const PID_KEY = '1002';

    /**
     * @const string PPPID_KEY
     */
    const PPPID_KEY = '1003';

    /**
     * Process ID.
     *
     * @var int
     */
    protected $id = 0;

    /**
     * Parent process ID.
     *
     * @var int
     */
    protected $parentId = 0;

    /**
     * Process PID.
     *
     * @var int
     */
    protected $pid = 0;

    /**
     * Publish progress process PID.
     *
     * @var int
     */
    protected $ppPid = 0;

    /**
     * Process status.
     *
     * @var int
     */
    protected $status = 4;

    /**
     * The sequence number of the class instance.
     *
     * @var int
     */
    protected $taskNumber = 0;

    /**
     * Get the sequence number of the class instance.
     *
     * @return int
     * @final
     */
    final public function getTaskNumber(): int
    {
        return $this->taskNumber;
    }

    /**
     * Set the sequence number of the class instance.
     *
     * @param int $taskNumber
     *
     * @return AdapterInterface
     * @final
     */
    final public function setTaskNumber(int $taskNumber): AdapterInterface
    {
        $this->taskNumber = $taskNumber;

        return $this;
    }

    /**
     * Get the ID of the parent process.
     *
     * @return int
     * @final
     */
    final public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * Set the ID of the parent process.
     *
     * @param int $id
     *
     * @return AdapterInterface
     * @final
     */
    final public function setParentId(int $id): AdapterInterface
    {
        $this->parentId = $id;

        return $this;
    }

    /**
     * Get the process id.
     *
     * @return int
     * @final
     */
    final public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set process id.
     *
     * @param int $id
     *
     * @return AdapterInterface
     * @final
     */
    final public function setId(int $id): AdapterInterface
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Generate a parent process id.
     *
     * @param AsyncTask $taskClass
     *
     * @throws AdapterException
     *
     * @return int
     * @final
     */
    final public function genarateParentId(AsyncTask $taskClass = null): int
    {
        $reflection = new \ReflectionClass($taskClass);
        $key = ftok($reflection->getFileName(), 'A');
        if (-1 === $key) {
            throw new AdapterException('The conversion of the project path and ID to the System V IPC key failed.');
        }

        return (int) $key;
    }

    /**
     * Generate a process id.
     *
     * @param AsyncTask $taskClass
     *
     * @return int
     * @final
     */
    final public function genarateId(AsyncTask $taskClass = null): int
    {
        return (int) $this->genarateParentId($taskClass).self::getLine().$this->getTaskNumber();
    }

    /**
     * Get the PID of the process.
     *
     * @return int
     * @final
     */
    final public function getPid(): int
    {
        return (int) $this->pid;
    }

    /**
     * Update PID process.
     *
     * @return AdapterInterface
     * @final
     */
    final public function updatePid(): AdapterInterface
    {
        $this->pid = getmypid();

        return $this->write(self::PID_KEY, $this->pid);
    }

    /**
     * Get the PID of the Publish Progress process.
     *
     * @return int
     * @final
     */
    final public function getPpPid(): int
    {
        if (0 == $this->ppPid && $this->has(self::PPPID_KEY)) {
            $this->ppPid = $this->get(self::PPPID_KEY);
        }

        return $this->ppPid;
    }

    /**
     * Update PID Publish Progress process.
     *
     * @return AdapterInterface
     * @final
     */
    final public function updatePpPid(): AdapterInterface
    {
        $this->ppPid = getmypid();

        return $this->write(self::PPPID_KEY, $this->ppPid);
    }

    /**
     * Set process status.
     *
     * @param int $status
     *
     * @return AdapterInterface
     * @final
     */
    final public function setStatus(int $status): AdapterInterface
    {
        $this->status = $status;

        return $this->write(self::STATUS_KEY, $status);
    }

    /**
     * Get process status.
     *
     * @return int
     * @final
     */
    final public function getStatus(): int
    {
        if ($this->has(self::STATUS_KEY)) {
            $status = $this->get(self::STATUS_KEY);
        } elseif (is_null($this->status)) {
            $status = $this->status = self::STATUS_UNDEFINED;
        } else {
            $status = $this->status;
        }

        return (int) $status;
    }

    /**
     * Checking the process for cancel status.
     *
     * @return bool
     * @final
     */
    final public function isCancelled(): bool
    {
        return self::STATUS_CANCELED == $this->getStatus();
    }

    /**
     * Write the variable.
     *
     * @param mixed $key
     * @param mixed $val
     *
     * @return AdapterInterface
     * @abstract
     */
    abstract public function write($key, $val): AdapterInterface;

    /**
     * Get the value of a variable.
     *
     * @param mixed $key
     *
     * @return mixed
     * @abstract
     */
    abstract public function get($key);

    /**
     * Check for variable.
     *
     * @param mixed $key
     *
     * @return bool
     * @abstract
     */
    abstract public function has($key): bool;

    /**
     * Get the line number of the class initialization.
     *
     * @return int
     * @abstract
     */
    private static function getLine(): int
    {
        $backtrace = debug_backtrace();

        foreach ($backtrace as $key => $trace) {
            if ('AsyncTask\AsyncTask' == $trace['class']) {
                if ($backtrace[$key + 1]['line'] > 0) {
                    return (int) $backtrace[$key + 1]['line'];
                }
                break;
            }
        }

        return 0;
    }
}
