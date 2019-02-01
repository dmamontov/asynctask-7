<?php

declare(strict_types=1);

namespace AsyncTask\Tests;

use AsyncTask\PidSystem;
use PHPUnit\Framework\TestCase;

final class PidSystemTest extends TestCase
{
    public function testCreatePid(): void
    {
        PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);

        $file = PidSystem::create(999999, 'Foo', PidSystem::TYPE_PID);

        $this->assertFileExists($file);

        $this->assertTrue(false !== stripos($file, (string) 999999));
        $this->assertTrue(false !== stripos($file, PidSystem::TYPE_PID));

        $this->assertEquals('Foo', file_get_contents($file));

        @unlink($file);
    }

    public function testCreatePpPid(): void
    {
        PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);

        $file = PidSystem::create(999999, 'Foo', PidSystem::TYPE_PPPID);

        $this->assertFileExists($file);

        $this->assertTrue(false !== stripos($file, (string) 999999));
        $this->assertTrue(false !== stripos($file, PidSystem::TYPE_PPPID));

        $this->assertEquals('Foo', file_get_contents($file));

        @unlink($file);
    }

    public function testRemovePid(): void
    {
        PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);

        $file = PidSystem::create(999999, 'Foo', PidSystem::TYPE_PID);

        $this->assertFileExists($file);

        PidSystem::remove(999999, PidSystem::TYPE_PID);

        $this->assertFileNotExists($file);
    }

    public function testRemovePpPid(): void
    {
        PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);

        $file = PidSystem::create(999999, 'Foo', PidSystem::TYPE_PPPID);

        $this->assertFileExists($file);

        PidSystem::remove(999999, PidSystem::TYPE_PPPID);

        $this->assertFileNotExists($file);
    }

    public function testOpenPidWithoutRemove(): void
    {
        PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);

        $fileOne = PidSystem::create(888888, 'Foo', PidSystem::TYPE_PID);
        $fileTwo = PidSystem::create(999999, 'Bar', PidSystem::TYPE_PID);

        $this->assertFileExists($fileOne);
        $this->assertFileExists($fileTwo);

        $pids = PidSystem::getOpenPids(PidSystem::FILTER_PID, false);
        $this->assertEquals(2, count($pids));

        foreach ($pids as $pid) {
            $this->assertTrue(in_array($pid, [888888, 999999]));
        }

        PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);
    }

    public function testOpenPidAndRemove(): void
    {
        PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);

        $fileOne = PidSystem::create(888888, 'Foo', PidSystem::TYPE_PID);
        $fileTwo = PidSystem::create(999999, 'Bar', PidSystem::TYPE_PID);

        $this->assertFileExists($fileOne);
        $this->assertFileExists($fileTwo);

        $pids = PidSystem::getOpenPids(PidSystem::FILTER_PID, true);
        $this->assertEquals(2, count($pids));

        foreach ($pids as $pid) {
            $this->assertTrue(in_array($pid, [888888, 999999]));
        }

        $this->assertFileNotExists($fileOne);
        $this->assertFileNotExists($fileTwo);
    }

    public function testOpenPpPidWithoutRemove(): void
    {
        PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);

        $fileOne = PidSystem::create(888888, 'Foo', PidSystem::TYPE_PPPID);
        $fileTwo = PidSystem::create(999999, 'Bar', PidSystem::TYPE_PPPID);

        $this->assertFileExists($fileOne);
        $this->assertFileExists($fileTwo);

        $pids = PidSystem::getOpenPids(PidSystem::FILTER_PPPID, false);
        $this->assertEquals(2, count($pids));

        foreach ($pids as $pid) {
            $this->assertTrue(in_array($pid, [888888, 999999]));
        }

        PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);
    }

    public function testOpenPpPidAndRemove(): void
    {
        PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);

        $fileOne = PidSystem::create(888888, 'Foo', PidSystem::TYPE_PPPID);
        $fileTwo = PidSystem::create(999999, 'Bar', PidSystem::TYPE_PPPID);

        $this->assertFileExists($fileOne);
        $this->assertFileExists($fileTwo);

        $pids = PidSystem::getOpenPids(PidSystem::FILTER_PPPID, true);
        $this->assertEquals(2, count($pids));

        foreach ($pids as $pid) {
            $this->assertTrue(in_array($pid, [888888, 999999]));
        }

        $this->assertFileNotExists($fileOne);
        $this->assertFileNotExists($fileTwo);
    }

    public function testOpenAllWithoutRemove(): void
    {
        PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);

        $fileOne = PidSystem::create(888888, 'Foo', PidSystem::TYPE_PID);
        $fileTwo = PidSystem::create(999999, 'Bar', PidSystem::TYPE_PPPID);

        $this->assertFileExists($fileOne);
        $this->assertFileExists($fileTwo);

        $pids = PidSystem::getOpenPids(PidSystem::FILTER_ALL, false);
        $this->assertEquals(2, count($pids));

        foreach ($pids as $pid) {
            $this->assertTrue(in_array($pid, [888888, 999999]));
        }

        PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);
    }

    public function testOpenAllAndRemove(): void
    {
        PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);

        $fileOne = PidSystem::create(888888, 'Foo', PidSystem::TYPE_PID);
        $fileTwo = PidSystem::create(999999, 'Bar', PidSystem::TYPE_PPPID);

        $this->assertFileExists($fileOne);
        $this->assertFileExists($fileTwo);

        $pids = PidSystem::getOpenPids(PidSystem::FILTER_ALL, true);
        $this->assertEquals(2, count($pids));

        foreach ($pids as $pid) {
            $this->assertTrue(in_array($pid, [888888, 999999]));
        }

        $this->assertFileNotExists($fileOne);
        $this->assertFileNotExists($fileTwo);
    }
}
