How-to inject runtime parameters
================================

A job can be invoked with two types of parameters. First type are parameters that can be serialized/deserialized with the [JMSSerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle). Second type are runtime parameters. As the name suggests, runtime parameters are provided at runtime. This is done with event listeners that register the runtime parameters within the execution context of a jobm, which is much simpler than it might sound.

__Note:__ The job logger, that is available for every job, is provided with this very same approach.

To inject a parameter at runtime you need to do two things:

1. Create a listener class
2. Register the listener class in the service container

## Step 1: Create a listener class

First you have to create the listener class that sets the runtime parameter into the execution context:

```php
class LoggerProviderJobListener
{
    private $factory;

    // ...

    public function onPreExecute(ExecutionEvent $event)
    {
        $event->getContext()->set('logger', $this->factory->create($event->getJob()));
    }
}
```

The event passed to the listener is of type [ExecutionEvent](../Event/ExecutionEvent.php) which provides access to the execution context of a job. This is a simple container where you can register parameters under a certain key. The example shows a snipped of the [LoggerProviderJobListener](../Listener/LoggerProviderJobListener.php) which provides the runtime parameter `@logger` that is available for each job.

## Step 2: Register the listener class in the service container

Next you need to register the listener in the service container and tag it:

```xml
<service id="my_job_listener" class="Abc\Bundle\JobBundle\Listener\LoggerProviderJobListener" public="true">
    <argument type="service">...</argument>
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

__Note:__ The name of the runtime parameter reference in the `@JobParameter` annotation must match key that the parameter was registered in the execution context.