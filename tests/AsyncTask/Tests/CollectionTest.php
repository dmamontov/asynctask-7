<?php

declare(strict_types=1);

namespace AsyncTask\Tests;

use AsyncTask\Collection;
use PHPUnit\Framework\TestCase;

final class CollectionTest extends TestCase
{
    public function testInstance(): void
    {
        $collection = new Collection();

        $this->assertInstanceOf(
            Collection::class,
            $collection
        );

        $this->assertInstanceOf(
            Collection::class,
            $collection->set('one', 'foo')
        );

        $this->assertInstanceOf(
            Collection::class,
            $collection->remove('one')
        );

        unset($collection);
    }

    public function testSetAndGet(): void
    {
        $collection = new Collection();

        $collection->set('one', 'foo');

        $this->assertEquals('foo', $collection->get('one'));
        $this->assertEquals('foo', $collection['one']);

        $collection['two'] = 'bar';

        $this->assertEquals('bar', $collection->get('two'));
        $this->assertEquals('bar', $collection['two']);

        unset($collection);
    }

    public function testHasAndRemove(): void
    {
        $collection = new Collection();

        $collection->set('one', 'foo');
        $collection->set('two', 'bar');

        $this->assertTrue($collection->has('one'));
        $this->assertTrue(isset($collection['one']));

        $collection->remove('one');

        $this->assertFalse($collection->has('one'));
        $this->assertFalse(isset($collection['one']));

        unset($collection['two']);

        $this->assertFalse($collection->has('two'));
        $this->assertFalse(isset($collection['two']));

        unset($collection);
    }

    public function testSetByArray(): void
    {
        $collection = new Collection(
            [
                'one' => 'foo',
                'two' => 'bar',
            ]
        );

        $this->assertTrue($collection->has('one'));
        $this->assertTrue(isset($collection['one']));
        $this->assertTrue($collection->has('two'));
        $this->assertTrue(isset($collection['two']));

        unset($collection);
    }

    public function testCount(): void
    {
        $collection = new Collection(
            [
                'one' => 'foo',
                'two' => 'bar',
            ]
        );

        $this->assertEquals(2, count($collection));

        unset($collection);
    }
}
