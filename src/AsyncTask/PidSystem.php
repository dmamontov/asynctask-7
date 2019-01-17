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
 * @since     File available since Release 2.0.0
 */

namespace DM\AsyncTask;

/**
 * The class is designed to work with temporary PID files.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2019 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 *
 * @version   Release: 2.0.0
 *
 * @see      https://github.com/dmamontov/asynctask
 * @since     Class available since Release 2.0.0
 */
class PidSystem
{
    /**
     * @const string TYPE_PID
     */
    const TYPE_PID = 'PID';

    /**
     * @const string TYPE_PPPID
     */
    const TYPE_PPPID = 'PPPID';

    /**
     * @const string FILTER_ALL
     */
    const FILTER_ALL = '.AT.';

    /**
     * @const string FILTER_PID
     */
    const FILTER_PID = '.PID.AT.';

    /**
     * @const string FILTER_PPPID
     */
    const FILTER_PPPID = '.PPPID.AT.';

    /**
     * Creates a temporary PID file with the specified content and type.
     *
     * @param int    $pid
     * @param string $content
     * @param string $type
     *
     * @return string
     */
    public static function create(int $pid, string $content = '', string $type = self::TYPE_PID): string
    {
        $file = tempnam(sys_get_temp_dir(), sprintf('%s.%s.AT.', $pid, $type));

        if (!empty($content)) {
            file_put_contents($file, $content);
        }

        return $file;
    }

    /**
     * Removes temporary PID files with the specified type.
     *
     * @param int    $pid
     * @param string $type
     */
    public static function remove(int $pid, string $type = self::TYPE_PID)
    {
        foreach (new \DirectoryIterator(sys_get_temp_dir()) as $file) {
            if (false === stripos($file->getFilename(), sprintf('%s.%s.AT.', $pid, $type))) {
                continue;
            }

            @unlink($file->getPathname());
        }
    }

    /**
     * Gets a list of open PID processes with the specified filter.
     *
     * @param string $filter
     * @param bool   $removeFile
     *
     * @return array
     */
    public static function getOpenPids(string $filter = self::FILTER_ALL, bool $removeFile = true): array
    {
        $pids = [];

        foreach (new \DirectoryIterator(sys_get_temp_dir()) as $file) {
            if (false === stripos($file->getFilename(), $filter)) {
                continue;
            }

            $chank = explode('.', $file->getFilename());
            $pids[] = $chank[0];

            if ($removeFile) {
                @unlink($file->getPathname());
            }
        }

        return $pids;
    }
}
