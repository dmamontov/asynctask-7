<?php

namespace AsyncTask\Example;

use AsyncTask\Adapter;
use AsyncTask\Interfaces\AdapterInterface;

class ExampleAdapter extends Adapter implements AdapterInterface
{
    public function init(): AdapterInterface
    {
        return $this;
    }

    public function finish(): AdapterInterface
    {
        return $this;
    }

    public function clean(bool $parent = false): AdapterInterface
    {
        return $this;
    }

    public function has($key, bool $parent = false): bool
    {
        return false;
    }

    public function remove($key, bool $parent = false): bool
    {
        return true;
    }

    public function get($key, bool $parent = false)
    {
        return null;
    }

    public function write($key, $val, bool $parent = false): AdapterInterface
    {
        return $this;
    }
}
