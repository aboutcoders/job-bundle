Cancel Jobs
===========

In some cases it is necessary to cancel a job either manually or if for example a new version of the application is deployed. By default a job cannot be processed at runtime unless a process termination signal kills the underlying PHP process.

To make job cancellable the job class must implement the interface ControllerAwareInterface:

```php
namespace Abc\ProcessControl;

use Abc\ProcessControl\ControllerInterface;

interface ControllerAwareInterface
{
    /**
     * @param ControllerInterface $controller
     * @return void
     */
    public function setController(ControllerInterface $controller);
}
```

This `ControllerAwareInterface` defines the method `doExit()` which indicates whether the job has been cancelled or whether e.g a TERM signal has been sent to the underlying PHP process:

```php
namespace Abc\ProcessControl;

interface ControllerInterface
{
    /**
     * Indicates whether to exit a process
     *
     * @return boolean
     */
    public function doExit();
}
```

__Note:__ It is recommended to implement this interface in every job that performs work for a longer period of time (e.g > 1 second) in order to prevent uncontrolled termination of jobs and in order support manual cancellation of jobs.

Below you see an example implementation how a job uses the controller:

```php

use Abc\ProcessControl\ControllerAwareInterface;
use Abc\ProcessControl\ControllerInterface;

class Sleeper implements ControllerAwareInterface
{
    /**
     * @var ControllerInterface
     */
    private $controller;

    /**
     * {@inheritdoc}
     */
    public function setController(ControllerInterface $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @param                 $seconds
     * @param LoggerInterface $logger
     * @ParamType({"integer", "@logger"})
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