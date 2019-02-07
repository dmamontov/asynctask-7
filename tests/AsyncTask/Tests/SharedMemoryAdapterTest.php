<?php

declare(strict_types=1);

use AsyncTask\Adapter;
use AsyncTask\Adapter\SharedMemoryAdapter;
use AsyncTask\Collection;
use AsyncTask\Example\ExampleTask;
use AsyncTask\Exception\AsyncTaskException;
use PHPUnit\Framework\TestCase;

final class SharedMemoryAdapterTest extends TestCase
{
    public function testInstance(): void
    {
        $task = new ExampleTask();

        $this->assertInstanceOf(
            SharedMemoryAdapter::class,
            $task->getAdapter()
        );
    }

    public function testMultiInstance(): void
    {
        $taskOne = new ExampleTask();
        $taskTwo = new ExampleTask();

        $this->assertNotEquals($taskOne->getAdapter()->getId(), $taskTwo->getAdapter()->getId());
        $this->assertEquals($taskOne->getAdapter()->getParentId(), $taskTwo->getAdapter()->getParentId());

        $idOne = 0;
        $idTwo = 0;

        $parentIdOne = 0;
        $parentIdTwo = 1;

        for ($i = 0; $i <= 1; ++$i) {
            $task = new ExampleTask(null, $i);

            switch ($i) {
                case 0:
                    $idOne = $task->getAdapter()->getId();
                    $parentIdOne = $task->getAdapter()->getParentId();
                    break;
                case 1:
                    $idTwo = $task->getAdapter()->getId();
                    $parentIdTwo = $task->getAdapter()->getParentId();
                    break;
            }
        }

        $this->assertNotEquals($idOne, $idTwo);
        $this->assertEquals($parentIdOne, $parentIdTwo);
    }

    public function testExecute(): void
    {
        $task = new ExampleTask();
        $task->setTitle('Test');

        $task->execute(new Collection());
        sleep(1);
        $this->assertEquals(Adapter::STATUS_RUNNING, $task->getStatus());
        sleep(2);

        $task->execute(new Collection());
        sleep(1);

        $this->expectException(AsyncTaskException::class);
        $task->execute(new Collection());
    }

    public function testStatusses(): void
    {
        $task = new ExampleTask();
        $task->setTitle('Test');

        $this->assertEquals(Adapter::STATUS_PENDING, $task->getStatus());
        $task->execute(new Collection());
        sleep(1);
        $this->assertEquals(Adapter::STATUS_RUNNING, $task->getStatus());
        sleep(3);
        $this->assertEquals(Adapter::STATUS_FINISHED, $task->getStatus());

        $task->execute(new Collection());
        sleep(1);
        $task->cancel();
        $this->assertEquals(Adapter::STATUS_CANCELED, $task->getStatus());

        $task = new ExampleTask();
        $task->setTitle('Test');
        $task->execute(new Collection());
        sleep(1);
        $task->cancel();
        $this->assertEquals(Adapter::STATUS_CANCELED, $task->getStatus());
        $this->assertTrue($task->isCancelled());
    }
}
