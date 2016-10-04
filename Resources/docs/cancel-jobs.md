Cancel Jobs
===========

In some cases it is necessary to cancel a job either manually or if for example a new version of the application is deployed. By default a job cannot be cancelled at runtime unless a process termination signal kills the underlying PHP process.

To make job cancellable the job class must implement the interface ControllerAwareInterface:

```php
namespace Abc\ProcessControl;

use Abc\ProcessControl\ControllerInterface;

interface ControllerAwareInterface
{
    public function setController(ControllerInterface $controller);
}
```

This `ControllerAwareInterface` defines the method `doExit()` which indicates whether the job should abort it's execution:

```php
namespace Abc\ProcessControl;

interface ControllerInterface
{
    public function doExit();
}
```

__Note:__ It is recommended to implement this interface in every job that performs work for a longer period of time (> 1 second) in order to prevent uncontrolled termination of jobs and in order support manual cancellation of jobs.

Below you see an example implementation how a job uses the controller:

```php
use Abc\ProcessControl\ControllerAwareInterface;
use Abc\ProcessControl\ControllerInterface;

class Sleeper implements ControllerAwareInterface
{
    private $controller;

    public function setController(ControllerInterface $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @ParamType("seconds", type="integer")
     * @ParamType("logger", type="@abc.logger")
     * @param integer $seconds
     * @param LoggerInterface $logger
     */
    public function sleep($seconds, LoggerInterface $logger)
    {
        // ...
    }
}
```