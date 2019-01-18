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

namespace AsyncTask\Adapter;

use AsyncTask\Adapter;
use AsyncTask\Exception\AdapterException;
use AsyncTask\Interfaces\AdapterInterface;

/**
 * Adapter based on Shared Memory.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2019 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 *
 * @version   Release: 2.0.2
 *
 * @see      https://github.com/dmamontov/asynctask
 * @since     Class available since Release 2.0.2
 */
class SharedMemoryAdapter extends Adapter implements AdapterInterface
{
    /**
     * Resource process.
     *
     * @var resource
     */
    public $resource;

    /**
     * Resource parent process.
     *
     * @var resource
     */
    public $parentResource;

    /**
     * At the end of the process clears temporary files and resources.
     */
    public function __destruct()
    {
        $this->clean();
    }

    /**
     * {@inheritdoc}
     */
    public function init(): AdapterInterface
    {
        $this
            ->attach($this->getId())
            ->attach($this->getParentId(), true);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function finish(): AdapterInterface
    {
        $this->remove(self::PID_KEY);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clean($parent = false): AdapterInterface
    {
        shm_remove($this->getResource());

        if ($parent) {
            shm_remove($this->getParentResource());
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key, bool $parent = false): bool
    {
        return !is_null($parent ? $this->getParentResource() : $this->getResource()) &&
            shm_has_var($parent ? $this->getParentResource() : $this->getResource(), self::stringToInt($key));
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key, bool $parent = false): bool
    {
        return $this->has($key, $parent) ?
            shm_remove_var($parent ? $this->getParentResource() : $this->getResource(), self::stringToInt($key)) :
            true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, bool $parent = false)
    {
        return $this->has($key, $parent)
            ? shm_get_var($parent ? $this->getParentResource() : $this->getResource(), self::stringToInt($key))
            : false;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AdapterException
     */
    public function write($key, $val, bool $parent = false): AdapterInterface
    {
        if (is_null($parent ? $this->getParentResource() : $this->getResource())) {
            return $this;
        }

        $result = shm_put_var($parent ? $this->getParentResource() : $this->getResource(), self::stringToInt($key), $val);

        if (false === $result) {
            throw new AdapterException(sprintf('Failed to put %s in shared memory %i.', $key, (int) ($parent ? $this->getParentResource() : $this->getResource())));
        }

        return $this;
    }

    /**
     * Converts a string to a numeric storage value in Shared Memory.
     *
     * @param string $str
     *
     * @return int
     */
    public static function stringToInt(string $str): int
    {
        $uid = '';

        for ($char = 0; $char < strlen($str); ++$char) {
            $uid .= ord($str[$char]);
        }

        return (int) $uid;
    }

    /**
     * Gets the parent resource.
     *
     * @return resource
     */
    private function getParentResource()
    {
        return $this->parentResource;
    }

    /**
     * Sets the parent resource.
     *
     * @param resource $resource
     *
     * @return AdapterInterface
     */
    private function setParentResource($resource): AdapterInterface
    {
        $this->parentResource = $resource;

        return $this;
    }

    /**
     * Gets the resource.
     *
     * @return resource
     */
    private function getResource()
    {
        return $this->resource;
    }

    /**
     * Sets the resource.
     *
     * @param resource $resource
     *
     * @return AdapterInterface
     */
    private function setResource($resource): AdapterInterface
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Opens up new resources.
     *
     * @param int  $id
     * @param bool $parent
     *
     * @throws AdapterException
     *
     * @return AdapterInterface
     */
    private function attach(int $id, bool $parent = false): AdapterInterface
    {
        $resource = shm_attach($id);

        if (false === $resource) {
            throw new AdapterException('Unable to create the shared memory segment.');
        }

        if ($parent) {
            $this->setParentResource($resource);
        } else {
            $this->setResource($resource);
        }

        return $this;
    }
}
