How-to make a job cancellable at runtime
========================================

Jobs can be cancelled in two ways: either the job is cancelled manually by invoking the cancel method of the manager or the job is cancelled by a process termination signal sent to the underlying PHP process. By default a job will always be processed unless a process termination signal kills the PHP process.

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

This `ControllerAwareInterface` defines the method `doExit()` which indicates whether the job has been cancelled or whether a TERM signal has been sent to the underlying PHP process:

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

__Note: It is recommended to implement this interface in every job in order to prevent uncontrolled termination of jobs and in order to make the job more responsive.__