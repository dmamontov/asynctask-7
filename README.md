[![Build Status](https://travis-ci.org/dmamontov/asynctask-7.svg?branch=master)](https://travis-ci.org/dmamontov/asynctask-7)
[![Latest Stable Version](https://poser.pugx.org/dmamontov/asynctask-7/v/stable.svg)](https://packagist.org/packages/dmamontov/asynctask-7)
[![License](https://poser.pugx.org/dmamontov/asynctask-7/license.svg)](https://packagist.org/packages/dmamontov/asynctask-7)
[![Total Downloads](https://poser.pugx.org/dmamontov/asynctask-7/downloads)](https://packagist.org/packages/dmamontov/asynctask-7)
[![PHP Classes](https://img.shields.io/badge/php-classes-blue.svg)](https://www.phpclasses.org/package/11064-PHP-Execute-parallel-task-using-a-sub-class.html)

AsyncTask
=========

AsyncTask enables proper and easy use of the thread. This class allows to perform background operations and publish results on the thread without having to manipulate threads and/or handlers.

[The early class](https://github.com/dmamontov/asynctask) implementation supports PHP 5.3, but does not support what is implemented in this class.


## Requirements
- `PHP` version `~7.1.0`
- Module installed `pcntl` and `posix`
- All functions `pcntl`, `posix` removed from the directive `disable_functions`


- `SharedMemoryAdapter`:
  - All functions `shm` removed from the directive `disable_functions`

## Installation

1) Install [composer](https://getcomposer.org/download/)

2) Follow in the project folder:
```bash
composer require dmamontov/asynctask-7 ~2.0.13
```

In config `composer.json` your project will be added to the library `dmamontov/asynctask-7`, who settled in the folder `vendor/`. In the absence of a config file or folder with vendors they will be created.

If before your project is not used `composer`, connect the startup file vendors. To do this, enter the code in the project:
```php
require 'path/to/vendor/autoload.php';
```

## Adapter list
* `SharedMemory` - working
* ~~`FileSystem`~~ - in the process
* ~~`Redis`~~ - in the process

**Offer adapters that are missing. We develop!**

## Examples

### Example of work
```php
use AsyncTask\{
    AsyncTask,
    Collection
};

class TestTask extends AsyncTask
{
    protected function onPreExecute(Collection $collection)
    {
    }

    protected function doInBackground(Collection $collection)
    {
        return 'My First Task';
    }

    protected function onPostExecute($result)
    {
        echo $result;
    }

    protected function publishProgress()
    {
        echo rand(0,9) . PHP_EOL;
    }
}

$task = new TestTask();
$task
    ->setTitle('TestTask')
    ->execute(new Collection);
```

### Task Example
```php
use AsyncTask\AsyncTask;
use AsyncTask\Collection;

class ExampleTask extends AsyncTask
{
    /**
     * Optional method.
     */
    protected function onPreExecute(Collection $collection)
    {
        return $collection;
    }

    /**
     * Required method.
     */
    protected function doInBackground(Collection $collection)
    {
        return $collection;
    }

    /**
     * Optional method.
     * With this method, an additional process is created.
     */
    protected function publishProgress()
    {
    }

    /**
     * Optional method.
     */
    protected function onPostExecute($result)
    {
    }

    /**
     * Optional method.
     */
    protected function onCancelled()
    {
    }
}
```

### Adapter Example
```php
use AsyncTask\Adapter;
use AsyncTask\Interfaces\AdapterInterface;

class ExampleAdapter extends Adapter implements AdapterInterface
{

    /**
     * Required method.
     */
    public function init(): AdapterInterface
    {
        return $this;
    }
    
    /**
     * Required method.
     */
    public function finish(): AdapterInterface
    {
        return $this;
    }

    /**
     * Required method.
     */
    public function clean(bool $parent = false): AdapterInterface
    {
        return $this;
    }
    
    /**
     * Required method.
     */
    public function has($key, bool $parent = false): bool
    {
        return false;
    }

    /**
     * Required method.
     */
    public function remove($key, bool $parent = false): bool
    {
        return true;
    }
    
    /**
     * Required method.
     */
    public function get($key, bool $parent = false)
    {
        return null;
    }

    /**
     * Required method.
     */
    public function write($key, $val, bool $parent = false): AdapterInterface
    {
        return $this;
    }
}
```
## ToDo
* More tests.
* More adapters.
* Class for managing running processes.
