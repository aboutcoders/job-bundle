Basic Usage
===========

## Executing a default job

In order to execute a default job registration of default jobs must be enabled in the configuration:

```yaml
# app/config/config.yml
abc_job:
    register_default_jobs: true
```

The following example sends an email:

```php
use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Abc\Bundle\JobBundle\Job\ManagerInterface;

// retrieve job manager from the container
$manager = $container->get('abc.job.manager');

// create a message
$message = new Message();
$message->setTo('mail@domain.tld');
$message->setFrom('mail@domain.tld');
$message->setSubject('Hello World');

// add job to the queue
$manager->add('abc_mailer', [$message]);
```

You can trigger processing of the job with the consumer command:

```bash
php bin/console abc:job:consume default --stop-when-empty
```

This will consume all messages from the default queue and process the associated jobs.

__Note:__ If you are using the job `abc.mailer` be aware that depending on the spool configuration emails are not sent until the kernel terminates.


## Creating a custom job

To create a custom job you have to create a class that will perform the actual work. This can be any kind of class.

```php
namespace My\Bundle\ExampleBundle\Job\MyJob;

use Abc\Bundle\JobBundle\Annotation\JobParameters;
use Psr\Log\LoggerInterface;

class MyJob
{
    /**
     * @ParamType("to", type="string")
     * @ParamType("logger", type="@abc.logger")
     * @ReturnType("string")
     */
    public function sayHello($to, LoggerInterface $logger)
    {
        $message = 'Hello ' . $to;
    
        $logger->info($message);
        
        return $message;
    }
}
```

If the method of the class that you want to invoke as job requires parameters or returns a value you need to annotate the method. Please note the annotations __@ParamType__ and __@ReturnType__. They are used to specify the type of parameters the method is invoked with as well as the type of the return value. Since jobs are executed in the background the parameters must be persisted and therefor serialized when a job is added to the queue. Serialization and deserialization is done using the [JMS Serializer](http://jmsyst.com/libs/serializer).

In the above example there is a special parameter `@abc.logger`. This parameter references a so called [runtime parameter](./runtime-parameters.md). In contrast to regular parameters which are serialized when a job is added to the queue runtime parameters are provided at runtime of a job using the event dispatcher.

__Note:__ Do not mix runtime parameters with service ids inside the dependency injection container. However you can of course inject any service from the service container as a runtime parameter.

The previous example uses the default runtime parameter `@abc.logger` that is provided by the AbcJobBundle. If this parameter is defined, a dedicated PSR compliant logger will be injected into the method which will log methods only for this job. Please refer to the chapter [Logging]() if you want to know more about the logging features.

### Step 2: Register the class in the service container

Next you have to register the job class as a service within the service container and tag it.

```yaml
# app/config/services.yml

services:
    my_job:
        class: My\Bundle\ExampleBundle\Job\MyJob
        tags:
            -  { name: "abc.job", type: "say_hello", method: "sayHello" }
```

The tag `abc.job` must define the two attributes `type` and `method` where `type` defines the unique type of the job (e.g. "abc_mailer") and `method` references the method of the class to be executed.

Now you can add the custom job to the queue:

```php
// retrieve job manager from the container
$manager = $container->get('abc.job.manager');

// add job to the queue
$job = $manager->addJob('say_hello', array('World'));
```

When the job was processed you can get the logs of the job:

```php
// get log messages of a job
$logs = $manager->getLogs($job->getTicket());
```