How-To inject runtime parameters
================================

A job can be invoked with two types of parameters. First are serializable parameters, that can be serialized/deserialized with the [JMSSerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle). Second are runtime parameters. Runtime parameters must be injected into the execution context of a job so that they can be passed to the job when the job gets invoked. The logger for example that is available for each job is provided with this very approach.

To inject a parameter at runtime you need to follow two steps:

1. Create a listener class
2. Register the listener class in the service container

## Step 1: Create a listener class

First you have to create the listener class that

```php
class LoggerProviderJobListener
{
    private $factory;

    public function onPreExecute(ExecutionEvent $event)
    {
        $event->getContext()->set('logger', $this->factory->create($event->getJob()));
    }
}
```

The event passed to the listener is of type [ExecutionEvent](../Event/ExecutionEvent.php) which provides access to the execution context of a job. This is a simple container where you can get/set parameters with a certain name. The example shows a snipped of an internal listener class of the AbcJobBundle that provides the logger for all jobs.

## Step 2: Register the listener class in the service container

Next you need to register the listener in the service container and tag it:

```xml
<service id="my_job_listener" class="Abc\Bundle\JobBundle\Listener\LoggerProviderJobListener" public="true">
    <tag name="abc.job.event_listener" event="abc.job.pre_execute" method="onPreExecute"/>
</service>
```

The runtime parameter now only needs to be referenced in the __@JobParameter__ annotation:

```php
/**
 * @JobParameters({"string", "@logger"})
 */
public function doSomething($string, Psr\Log\LoggerInterface $logger) {

}
```