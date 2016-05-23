How-to make a job cancellable at runtime
========================================

A job can be cancelled in two cases: the job is cancelled manually or the job is cancelled externally by a process termination signal. If your job does not take this into account, it will either still be processed, although it has been cancelled or it will be killed at a certain point by some external process control system.

In order to make a job terminate gracefully or to make a job more responsive the job class must implement the interface ControllerAwareInterface:

```php
namespace Abc\ProcessControl;

interface ControllerAwareInterface
{
    /**
     * @param Controller $controller
     * @return void
     */
    public function setController(Controller $controller);
}
```

This `Controller` defines one method `doExit()` that can be used to determine whether the job should terminate:

```php

namespace Abc\ProcessControl;

interface Controller
{
    /**
     * Indicates whether to exit a process
     *
     * @return boolean
     */
    public function doExit();
}
```

Below you see an example implementation how a job uses the controller:

```php
use Abc\ProcessControl\ControllerAwareInterface;

class Sleeper implements ControllerAwareInterface
{
    /**
     * @var Controller
     */
    private $controller;

    /**
     * {@inheritdoc}
     */
    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @param                 $seconds
     * @param LoggerInterface $logger
     * @JobParameters({"integer", "@logger"})
     */
    public function sleep($seconds, LoggerInterface $logger)
    {
        do {
            $logger->info('sleep for 1 second');

            sleep(1);
            $seconds--;

            $logger->info('determine whether to terminate');
        } while ($seconds > 0 && !$this->controller->doExit());
    }
}
```