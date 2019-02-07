<?php

namespace AsyncTask\Example;

use AsyncTask\AsyncTask;
use AsyncTask\Collection;

class ExampleTask extends AsyncTask
{
    protected function onPreExecute(Collection $collection)
    {
        return $collection;
    }

    protected function doInBackground(Collection $collection)
    {
        sleep(1);

        return $collection;
    }

    protected function publishProgress()
    {
    }

    protected function onPostExecute($result)
    {
    }

    protected function onCancelled()
    {
    }
}
