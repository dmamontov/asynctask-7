[![Build Status](https://travis-ci.org/dmamontov/asynctask-7.svg?branch=master)](https://travis-ci.org/dmamontov/asynctask-7)
[![Latest Stable Version](https://poser.pugx.org/dmamontov/asynctask-7/v/stable.svg)](https://packagist.org/packages/dmamontov/asynctask-7)
[![License](https://poser.pugx.org/dmamontov/asynctask-7/license.svg)](https://packagist.org/packages/dmamontov/asynctask-7)
[![Total Downloads](https://poser.pugx.org/dmamontov/asynctask-7/downloads)](https://packagist.org/packages/dmamontov/asynctask-7)

AsyncTask
=========

AsyncTask enables proper and easy use of the thread. This class allows to perform background operations and publish results on the thread without having to manipulate threads and/or handlers. [More information](https://dmamontov.github.io/asynctask-7).


## Requirements
* PHP version ~7.0.0
* Module installed pcntl and posix
* All functions pcntl, posix and shm removed from the directive disable_functions

## Installation

1) Install [composer](https://getcomposer.org/download/)

2) Follow in the project folder:
```bash
composer require dmamontov/asynctask-7 ~2.0.0
```

In config `composer.json` your project will be added to the library `dmamontov/asynctask-7`, who settled in the folder `vendor/`. In the absence of a config file or folder with vendors they will be created.

If before your project is not used `composer`, connect the startup file vendors. To do this, enter the code in the project:
```php
require 'path/to/vendor/autoload.php';
```

### Example of work
```php
use DM\AsyncTask\{
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
