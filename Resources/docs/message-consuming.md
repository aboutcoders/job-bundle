Message Consuming
=================

There are different ways to consume messages from the queues and process the jobs.

## Command Line

The AbcJobBundle provides the symfony command `abc:job:consumer`. The command requires the name of the queue that messages will be consumed from as argument. The following command will consume messages from the `default` queue. 

```bash
php bin/console abc:job:consume default
```

In order to prevent out of memory errors the command should always be invoked with the option `max-iterations`.

```bash
php bin/console abc:job:consume default --max-iterations=250
```

## PHP

The consumer command uses the underlying service `abc.job.consumer` to do its work. You can also use this service to consume and process jobs from the queue.

```php
// retrieve job manager from the container
$consumer = $container->get('abc.job.consumer');

$consumer->consume('default', [
    'max-iterations' => 250
])
```

## Supervisor

In a production environment it is recommended to use a process control system like [supervisor](http://supervisord.org/) to monitor consumers and restart a process as soon as it stopped.

In case you decided to use [supervisor](http://supervisord.org/) you might consider using the following bundles:

* [YZSupervisorBundle](https://github.com/yzalis/SupervisorBundle)
* [AbcSupervisorCommandBundle](https://github.com/aboutcoders/supervisor-command-bundle)

Next Step: [Job Management](./job-management.md)