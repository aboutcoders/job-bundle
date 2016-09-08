Logging
=======

During the execution of a job each job has access to it's own standard PSR logger.

There are two ways to inject the logger into the job class.

## Injecting the logger as a runtime parameter

To inject the logger as a runtime parameter you simply have to specify the `@abc.job.logger` in the `@ParamType` annotation of the method and add the logger to the method signature:

```php
namespace My\Bundle\ExampleBundle\Job\MyJob;

class MyJob
{
    /**
     * @ParamType({"@abc.job.logger"})
     */
    public function doSomething(Psr\Log\LoggerInterface $logger)
    {
        $logger->info('Hello World');
    }
}
```

This approach is especially useful if your job class defines more than one job.

## Injecting the logger using the LoggerAwareInterface

Another option is to inject the logger by making the job class implement the interface `Psr\Log\LoggerInterfaceInterface`:

```php
namespace My\Bundle\ExampleBundle\Job\MyJob;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class MyJob implements LoggerAwareInterface
{
    protected $logger;
    
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function doSomething()
    {
        $this->logger->info('Hello World');
    }
}
```

## Getting the logs of a job

Use the following command to get to logs of a job:

```php
$records = $manager->getLogs($job->getTicket());
```

This will return an array of log records that are available for this job.

__Note:__ Depending on the configuration the records are internally stored either on the filesystem in the JSON format or in the database.

Please refer to the chapter [Configuration](./configuration.md) to get information about the available configuration options.