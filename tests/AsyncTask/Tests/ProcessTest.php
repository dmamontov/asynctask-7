<?php

declare(strict_types=1);

namespace AsyncTask\Tests;

use AsyncTask\Exception\ProcessException;
use AsyncTask\PidSystem;
use AsyncTask\Process;
use PHPUnit\Framework\TestCase;

final class ProcessTest extends TestCase
{
    public function testInstance(): void
    {
        $this->assertTrue(function_exists('pcntl_async_signals'));

        if (function_exists('pcntl_async_signals')) {
            $this->assertInstanceOf(
                Process::class,
                new Process()
            );

            $this->assertTrue(pcntl_async_signals());
        }
    }

    public function testTitle(): void
    {
        $process = new Process();

        if (PHP_SAPI !== 'cli') {
            $this->expectException(ProcessException::class);

            $process->setTitle('Foo');
        } else {
            $this->assertTrue(function_exists('cli_set_process_title'));

            if (function_exists('cli_set_process_title')) {
                try {
                    $process->setTitle('Foo');
                    $this->assertEquals('Foo', cli_get_process_title());
                } catch (Exception $e) {
                    $this->assertInstanceOf(
                        ProcessException::class,
                        $e
                    );
                }
            }
        }
    }

    public function testUntie(): void
    {
        $this->assertTrue(function_exists('pcntl_signal'));

        if (function_exists('pcntl_signal')) {
            $process = new Process();

            try {
                $process->untie();
            } catch (Exception $e) {
                $this->assertInstanceOf(
                    ProcessException::class,
                    $e
                );
            }
        }
    }

    public function testFork(): void
    {
        $this->assertTrue(function_exists('pcntl_fork'));

        if (function_exists('pcntl_fork')) {
            $process = new Process();

            $pid = true;
            try {
                if ($pid = $process->fork()) {
                    exit();
                }
            } catch (Exception $e) {
                $this->assertInstanceOf(
                    ProcessException::class,
                    $e
                );
            }

            $this->assertTrue(!$pid);
        }
    }

    public function testKill(): void
    {
        $this->assertTrue(function_exists('posix_kill'));

        if (function_exists('posix_kill')) {
            PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);

            $process = new Process();

            $pid = true;
            try {
                $process->untie();
                if ($pid = $process->fork()) {
                    PidSystem::create(getmypid(), 'Foo');
                    sleep(10);
                    exit();
                }
            } catch (Exception $e) {
                $this->assertInstanceOf(
                    ProcessException::class,
                    $e
                );
            }

            if (!$pid) {
                sleep(1);
                $pidOpens = PidSystem::getOpenPids(PidSystem::FILTER_PID, false);
                $this->assertEquals(1, count($pidOpens));

                $pidOpens = reset($pidOpens);
                $process->kill((int) $pidOpens);

                $pidOpens = PidSystem::getOpenPids(PidSystem::FILTER_PID, false);

                $this->assertEquals(0, count($pidOpens));
            }
        }
    }

    public function testKillAll(): void
    {
        $this->assertTrue(function_exists('posix_kill'));

        if (function_exists('posix_kill')) {
            PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);

            $process = new Process();

            $pidOne = true;
            $pidTwo = true;

            try {
                $process->untie();
                if ($pidOne = $process->fork()) {
                    PidSystem::create(getmypid(), 'Foo');
                    sleep(10);
                    exit();
                }
            } catch (Exception $e) {
                $this->assertInstanceOf(
                    ProcessException::class,
                    $e
                );
            }

            try {
                $process->untie();
                if ($pidTwo = $process->fork()) {
                    PidSystem::create(getmypid(), 'Bar');
                    sleep(10);
                    exit();
                }
            } catch (Exception $e) {
                $this->assertInstanceOf(
                    ProcessException::class,
                    $e
                );
            }

            if (!$pidOne && !$pidTwo) {
                sleep(1);
                $pidOpens = PidSystem::getOpenPids(PidSystem::FILTER_PID, false);
                $this->assertEquals(2, count($pidOpens));

                $process->killAll();

                $pidOpens = PidSystem::getOpenPids(PidSystem::FILTER_PID, false);

                $this->assertEquals(0, count($pidOpens));
            }
        }
    }
}
