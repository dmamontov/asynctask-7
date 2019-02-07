<?php

declare(strict_types=1);

namespace AsyncTask\Tests;

use AsyncTask\Adapter;
use AsyncTask\Example\ExampleAdapter;
use AsyncTask\Example\ExampleTask;
use AsyncTask\Interfaces\AdapterInterface;
use PHPUnit\Framework\TestCase;

final class AdapterTest extends TestCase
{
    public function testInstance(): void
    {
        $this->assertInstanceOf(
            AdapterInterface::class,
            new ExampleAdapter()
        );

        $this->assertInstanceOf(
            Adapter::class,
            new ExampleAdapter()
        );
    }

    public function testTaskNumber(): void
    {
        $adapter = new ExampleAdapter();

        $this->assertEquals(0, $adapter->getTaskNumber());

        $this->assertInstanceOf(
            AdapterInterface::class,
            $adapter->setTaskNumber(777)
        );

        $this->assertEquals(777, $adapter->getTaskNumber());
    }

    public function testLine(): void
    {
        $this->assertTrue(function_exists('ftok'));

        if (function_exists('ftok')) {
            $task = new ExampleTask(new ExampleAdapter());

            $id = $task->getAdapter()->getId();

            $this->assertEquals(47, (int) substr((string) $id, -3, 2));
        }
    }

    public function testParentId(): void
    {
        $this->assertTrue(function_exists('ftok'));

        if (function_exists('ftok')) {
            $adapter = new ExampleAdapter();

            $this->assertEquals(0, $adapter->getParentId());
            $task = new ExampleTask($adapter);

            $token = (int) ftok(realpath(__DIR__.'/../Example/ExampleTask.php'), 'A');
            $this->assertEquals($token, $task->getAdapter()->getParentId());

            $task->getAdapter()->setParentId(777777);
            $this->assertEquals(777777, $task->getAdapter()->getParentId());
        }
    }

    public function testId(): void
    {
        $this->assertTrue(function_exists('ftok'));

        if (function_exists('ftok')) {
            $adapter = new ExampleAdapter();

            $this->assertEquals(0, $adapter->getId());
            $task = new ExampleTask($adapter, 777);

            $id = $task->getAdapter()->getId();

            $this->assertEquals(777, (int) substr((string) $id, -3, 3));
            $this->assertEquals(81, (int) substr((string) $id, -5, 2));

            $token = (int) ftok(realpath(__DIR__.'/../Example/ExampleTask.php'), 'A');
            $this->assertEquals($token, (int) substr((string) $id, 0, -5));

            $task->getAdapter()->setId(777777);
            $this->assertEquals(777777, $task->getAdapter()->getId());
        }
    }

    public function testPid(): void
    {
        $adapter = new ExampleAdapter();

        $this->assertEquals(0, $adapter->getPid());

        $adapter->updatePid();

        $this->assertEquals(getmypid(), $adapter->getPid());
    }

    public function testPpPid(): void
    {
        $adapter = new ExampleAdapter();

        $this->assertEquals(0, $adapter->getPpPid());

        $adapter->updatePpPid();

        $this->assertEquals(getmypid(), $adapter->getPpPid());
    }

    public function testStatus(): void
    {
        $adapter = new ExampleAdapter();

        $this->assertEquals(Adapter::STATUS_UNDEFINED, $adapter->getStatus());

        $adapter->setStatus(Adapter::STATUS_PENDING);

        $this->assertEquals(Adapter::STATUS_PENDING, $adapter->getStatus());

        $adapter->setStatus(Adapter::STATUS_CANCELED);

        $this->assertTrue($adapter->isCancelled());
    }
}
