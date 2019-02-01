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
 * @since     File available since Release 2.0.2
 */

namespace AsyncTask;

use AsyncTask\Adapter\SharedMemoryAdapter;
use AsyncTask\Exception\AsyncTaskException;
use AsyncTask\Interfaces\AdapterInterface;

/**
 * AsyncTask enables proper and easy use of the thread. This class allows to perform background operations and publish results on the thread without having to manipulate threads and/or handlers.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2019 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 *
 * @version   Release: 2.0.2
 *
 * @see      https://github.com/dmamontov/asynctask
 * @since     Class available since Release 2.0.2
 * @abstract
 */
abstract class AsyncTask
{
    /**
     * Adapter instance.
     *
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * The sequence number of the class instance in the process.
     *
     * @var int
     */
    private $taskNumber;

    /**
     * The title for the process.
     *
     * @var string
     */
    private $title = '';

    /**
     * Update frequency for publishing progress in milliseconds.
     *
     * @var int
     */
    private $progressDelay = 1000000;

    /**
     * Creates a new asynchronous task.
     *
     * @param AdapterInterface $adapter
     * @param int              $taskNumber
     * @final
     */
    final public function __construct(AdapterInterface $adapter = null, int $taskNumber = 0)
    {
        if (is_null($adapter)) {
            $adapter = new SharedMemoryAdapter();
        }

        $this
            ->setTaskNumber($taskNumber)
            ->init($adapter);
    }

    /**
     * Executes the task with the specified parameters.
     *
     * @param mixed      $parameters
     * @param Collection $collection
     *
     * @throws AsyncTaskException
     *
     * @return AsyncTask
     * @final
     */
    final public function execute(Collection $collection = null): AsyncTask
    {
        if (Adapter::STATUS_RUNNING == $this->getStatus()) {
            throw new AsyncTaskException('The previous process is not yet complete.');
        }

        $process = new Process();
        $process->untie();

        if ($process->fork()) {
            $this->getAdapter()
                ->init()
                ->setStatus(Adapter::STATUS_RUNNING)
                ->updatePid();

            if (!empty($this->getTitle())) {
                $process->setTitle($this->getTitle());
            }

            PidSystem::create(
                $this->getAdapter()->getPid(),
                sprintf(
                    '%s:%s',
                    get_class($this->getAdapter()),
                    $this->getAdapter()->getId()
                )
            );

            if (method_exists($this, 'publishProgress')) {
                if ($process->fork()) {
                    $this->getAdapter()->updatePpPid();

                    if (!empty($this->getTitle())) {
                        $process->setTitle(sprintf('%s (progress)', $this->getTitle()));
                    }

                    PidSystem::create(
                        $this->getAdapter()->getPpPid(),
                        '',
                        PidSystem::TYPE_PPPID
                    );

                    $startTime = new \DateTime('now');
                    while (true) {
                        if (((new \DateTime('now'))->diff($startTime)->format('%s') * 1000000) < $this->getProgressDelay()) {
                            continue;
                        }

                        $startTime = new \DateTime('now');

                        $this->publishProgress();
                    }
                    exit();
                }
            }

            $preResult = $this->onPreExecute($collection);
            if (!is_null($preResult) && $preResult instanceof Collection) {
                $collection = $preResult;
            }

            $result = $this->doInBackground($collection);

            $this->onPostExecute($result);

            if ($this->getAdapter()->getPpPid() > 0) {
                PidSystem::remove($this->getAdapter()->getPpPid(), PidSystem::TYPE_PPPID);
                $process->kill($this->getAdapter()->getPpPid());
            }

            $this
                ->getAdapter()
                ->setStatus(Adapter::STATUS_FINISHED)
                ->finish();

            PidSystem::remove($this->getAdapter()->getPid(), PidSystem::TYPE_PID);

            $this->getAdapter()->clean(
                count(PidSystem::getOpenPids(PidSystem::FILTER_PID, false)) < 1
            );

            exit();
        }

        return $this;
    }

    /**
     * Attempts to cancel execution of this task.
     *
     * @return bool
     * @final
     */
    final public function cancel(): bool
    {
        if (!$this->getAdapter()->has(Adapter::PID_KEY)) {
            return true;
        }

        $this->onCancelled();

        PidSystem::remove($this->getAdapter()->get(Adapter::PID_KEY), PidSystem::TYPE_PID);

        $process = new Process();
        $process->kill($this->getAdapter()->get(Adapter::PID_KEY));

        if ($this->getAdapter()->has(Adapter::PPPID_KEY)) {
            PidSystem::remove($this->getAdapter()->get(Adapter::PPPID_KEY), PidSystem::TYPE_PPPID);
            $process->kill($this->getAdapter()->get(Adapter::PPPID_KEY));
            $this->getAdapter()->remove(Adapter::PPPID_KEY);
        }

        $this
            ->getAdapter()
            ->setStatus(Adapter::STATUS_CANCELED)
            ->finish();

        $this->getAdapter()->clean(
            count(PidSystem::getOpenPids(PidSystem::FILTER_PID), false) < 1 &&
            count(PidSystem::getOpenPids(PidSystem::FILTER_PPPID), false) < 1
        );

        return true;
    }

    /**
     * Returns the current status of this task.
     *
     * @return int
     * @final
     */
    final public function getStatus(): int
    {
        return $this->getAdapter()->getStatus();
    }

    /**
     * Returns true if this task was cancelled before it completed normally.
     *
     * @return bool
     * @final
     */
    final public function isCancelled(): bool
    {
        return $this->getAdapter()->isCancelled();
    }

    /**
     * Waiting for completion of the process.
     *
     *
     * @return bool
     * @final
     */
    final public function wait(): bool
    {
        if ($this->getAdapter()->has(Adapter::PID_KEY)) {
            $process = new Process();

            return $process->wait((int) $this->getAdapter()->get(Adapter::PID_KEY));
        }
        
        return true;
    }

    /**
     * Gets the refresh rate for publication progress in milliseconds.
     *
     * @return int
     * @final
     */
    final public function getProgressDelay(): int
    {
        return $this->progressDelay;
    }

    /**
     * Sets the refresh rate for publication progress in milliseconds.
     *
     * @param int $progressDelay
     *
     * @return AsyncTask
     * @final
     */
    final public function setProgressDelay(int $progressDelay): AsyncTask
    {
        $this->progressDelay = $progressDelay;

        return $this;
    }

    /**
     * Gets the process header.
     *
     * @return string
     * @final
     */
    final public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the process header.
     *
     * @param string $title
     *
     * @return AsyncTask
     * @final
     */
    final public function setTitle(string $title): AsyncTask
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets the adapter.
     *
     * @return AdapterInterface
     * @final
     */
    final public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Gets the ordinal number of the class instance in the process.
     *
     * @return int
     * @final
     */
    final public function getTaskNumber(): int
    {
        return $this->taskNumber;
    }

    /**
     * Sets the sequence number of the class instance in the process.
     *
     * @param int $taskNumber
     *
     * @return int
     * @final
     */
    final public function setTaskNumber(int $taskNumber): AsyncTask
    {
        $this->taskNumber = $taskNumber;

        return $this;
    }

    /**
     * Runs on the thread before doInBackground(Collection $collection).
     *
     * @param Collection $collection
     */
    protected function onPreExecute(Collection $collection)
    {
    }

    /**
     * Override this method to perform a computation on a background thread.
     *
     * @param Collection $collection
     *
     * @return mixed
     * @abstract
     */
    abstract protected function doInBackground(Collection $collection);

    /**
     * Runs on the thread after doInBackground(Collection $collection).
     *
     * @param mixed $result
     */
    protected function onPostExecute($result)
    {
    }

    /**
     * Runs on the thread after cancel().
     */
    protected function onCancelled()
    {
    }

    /**
     * Initializes the adapter.
     *
     * @param AdapterInterface $adapter
     *
     * @return AsyncTask
     */
    private function init(AdapterInterface $adapter): AsyncTask
    {
        $adapter
            ->setTaskNumber($this->getTaskNumber())
            ->setParentId($adapter->genarateParentId($this))
            ->setId($adapter->genarateId($this))
            ->init()
            ->setStatus(Adapter::STATUS_PENDING);

        $this->setAdapter($adapter);

        return $this;
    }

    /**
     * Sets the adapter.
     *
     * @param AdapterInterface $adapter
     *
     * @return AsyncTask
     */
    private function setAdapter(AdapterInterface $adapter): AsyncTask
    {
        $this->adapter = $adapter;

        return $this;
    }
}
