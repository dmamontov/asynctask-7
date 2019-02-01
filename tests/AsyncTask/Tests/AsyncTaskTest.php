<?php

declare(strict_types=1);

namespace AsyncTask\Tests;

use AsyncTask\Adapter;
use AsyncTask\Adapter\SharedMemoryAdapter;
use AsyncTask\AsyncTask;
use AsyncTask\Exception\AsyncTaskException;
use AsyncTask\Example\ExampleAdapter;
use AsyncTask\Example\ExampleTask;
use PHPUnit\Framework\TestCase;

final class AsyncTaskTest extends TestCase
{
    public function testInstance(): void
    {
        $this->assertInstanceOf(
            AsyncTask::class,
            new ExampleTask(new ExampleAdapter())
        );
    }

    public function testAdapter(): void
    {
        $this->assertTrue(function_exists('ftok'));

        if (function_exists('ftok')) {
            $task = new ExampleTask(new ExampleAdapter());

            $this->assertInstanceOf(
                ExampleAdapter::class,
                $task->getAdapter()
            );

            $method = new \ReflectionMethod($task, 'setAdapter');
            $method->setAccessible(true);

            $task = $method->invoke($task, new SharedMemoryAdapter());

            $this->assertInstanceOf(
                SharedMemoryAdapter::class,
                $task->getAdapter()
            );
        }
    }

    public function testTaskNumber(): void
    {
        $this->assertTrue(function_exists('ftok'));

        if (function_exists('ftok')) {
            for ($i = 1; $i <= 5; ++$i) {
                $task = new ExampleTask(new ExampleAdapter(), $i);

                $this->assertEquals($i, $task->getTaskNumber());
            }

            $task = new ExampleTask(new ExampleAdapter());
            $this->assertEquals(0, $task->getTaskNumber());
            $task->setTaskNumber(5);
            $this->assertEquals(5, $task->getTaskNumber());
        }
    }

    public function testStatus(): void
    {
        $this->assertTrue(function_exists('ftok'));

        if (function_exists('ftok')) {
            $task = new ExampleTask(new ExampleAdapter());

            $this->assertEquals(Adapter::STATUS_PENDING, $task->getStatus());

            $task->getAdapter()->setStatus(Adapter::STATUS_RUNNING);

            $this->expectException(AsyncTaskException::class);
            $task->execute();

            $task->getAdapter()->setStatus(Adapter::STATUS_CANCELED);
            $this->assertTrue($task->isCancelled());
        }
    }

    public function testTitle(): void
    {
        $this->assertTrue(function_exists('ftok'));

        if (function_exists('ftok')) {
            $task = new ExampleTask(new ExampleAdapter());

            $this->assertEmpty($task->getTitle());

            $task->setTitle('Foo');

            $this->assertEquals('Foo', $task->getTitle());
        }
    }

    public function testProgressDelay(): void
    {
        $this->assertTrue(function_exists('ftok'));

        if (function_exists('ftok')) {
            $task = new ExampleTask(new ExampleAdapter());

            $this->assertEquals(1000000, $task->getProgressDelay());

            $task->setProgressDelay(777);

            $this->assertEquals(777, $task->getProgressDelay());
        }
    }

    public function testWait(): void
    {
        $this->assertTrue(function_exists('ftok'));
        $this->assertTrue(function_exists('pcntl_waitpid'));

        if (function_exists('ftok') && function_exists('pcntl_waitpid')) {
            $task = new ExampleTask(new ExampleAdapter());

            $this->assertTrue($task->wait());
        }
    }
}
