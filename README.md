AbcJobBundle
============

A symfony bundle that allows you to process jobs asynchronously, by simply annotating the method of a class and registering it within the service container.

## Overview

This bundle provides the following features:

- Asynchronous job processing
- Scheduled execution of jobs
- JSON REST-Api
- Message Queue Backend based on RabbitMQ or Doctrine

## Disclaimer

This bundle is still under development. At the current moment we do not consider this bundle as stable and we feel free to change things at any level.

However we greatly appreciate if you decide to use this bundle and we appreciate your feedback, suggestions or even contributions.

## Installation

### Add the AbcJobBundle using composer

```json
{
    "require": {
        "aboutcoders/job-bundle": "annotation"
    }
}
```

### Update AppKernel.php of your symfony application

Add the following bundles to your kernel bootstrap sequence, in the `$bundles` array.

```php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Abc\Bundle\JobBundle\AbcJobBundle(),
    );

    return $bundles;
}
```

### Install third party bundles

Please follow the installation instructions of the following third party bundles:

* [AbcSchedulerBundle](https://bitbucket.org/hasc/scheduler-bundle)
* [AbcProcessControlBundle](https://bitbucket.org/hasc/process-control-bundle)
* [SonataNotificationBundle](https://github.com/sonata-project/SonataNotificationBundle)
* [JMSSerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle)
* [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle)
* [FOSRestBundle](https://github.com/FriendsOfSymfony/FOSRestBundle)
* [SensioFrameworkExtraBundle](http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle)
* [YZSupervisorBundle](https://github.com/yzalis/SupervisorBundle)

## Configuration

### Configure doctrine orm

At the current point only doctrine is supported as ORM. However by changing the configuration you can use a different persistence layer.

```yaml
abc_job:
  db_driver: orm
```

### Register a doctrine mapping type for a job status

```yaml
doctrine:
    dbal:

        types:
            abc.job.status: Abc\Bundle\JobBundle\Doctrine\Types\StatusType
```

### Register GDMO Timestampable

The bundle makes use of the GDMO Timestampable behavior. There are different approaches on how you can set up this behavior. Please refer to the [official symfony documentation](http://symfony.com/doc/current/cookbook/doctrine/common_extensions.html) and follow the instructions there.

### Import AbcJobBundle routing files

If you want to work with the REST-API you have to import the routing files.

```yaml
abc-rest-job:
    type: rest
    resource: "@AbcJobBundle/Resources/config/routing/rest-job.yml"
    prefix: /api
```

### Update the database schema

Finally you need to update your database schema in order to create the required tables.

```bash
php app/console doctrine:schema:update --force
```

## Basic Usage

### Registering a new job

In order to register a new job, you have to take two steps:

- Create the job class (that performs the actual work)
- Register the class in the service

#### Step 1: Create the job class

Create the class that performs the actual job. This can be any kind of class.

```php
namespace My\Bundle\ExampleBundle\Job\MyJob;

use Abc\Bundle\JobBundle\Annotation\JobParameters;
use Abc\Bundle\JobBundle\Annotation\JobResponse;

class MyJob
{
    /**
     * @JobParameters({"string", "@logger"})
     * @JobResponse("string")
     */
    public function hello($whom, Logger $logger)
    {
        $logger->debug('Hello ' . $whom);

        return 'Hello ' . $whom;
    }
}
```

Please note the two annotations __@JobParameters__ and __@JobResponse__. Both parameters and response (return value) of a job must be serializable and deserializable using the [JMS Serializer](http://jmsyst.com/libs/serializer).

The example uses a special parameter `@logger` which is a [runtime parameter](./docs/howto-inject-runtime-parameters.md). Each job can log to its own dedicated logger. All you have to do is specifying the parameter `@logger` within the __@JobParameters__.

__Note:__ You only have to provide the __@JobParameters__ or __@JobResponse__ in case your job requires parameters or returns a response.


#### Step 2: Register the class in the service

Next you have to register the job as a service within the service container by tagging it.

##### Using XML
```xml
<?xml version="1.0" ?>
<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
    <services>
        <service id="my_job" class="My\Bundle\ExampleBundle\Job\MyJob">
            <tag name="abc.job" type="my_job" method="doSomething"/>
        </service>
    </services>
</container>
```

##### Using YML

```yaml
services:
    my_job:
        class: My\Bundle\ExampleBundle\Job\MyJob
        tags:
            -  { name: abc.job, type: "my_job" method: "doSomething" }
```

The tag must define the attributes `name`, `type` and `method` where besides the tag name `type` specifies the unique name of the job (e.g. "mailer") and `method` references the method of the class to be executed.

### Adding a job for asynchronous processing

To execute a job asynchronously you need to retrieve the job manager from the service container and add the job.

```php
$job = $container->get('abc.job.manager')->addJob('my_job', array('Hello ' 'World!'));
```

The first argument of `addJob` specifies the type (unique name) of the job. This value must equal `type` that was chosen in the service tag. The second (optional) argument is an array of parameters the job will be executed with.

The return value is an implementation of [JobInterface](./Job/JobInterface.php). The most important attribute here is the job ticket. You can retrieve it like follows:

```php
$ticket = $job->getTicket();
```

The job ticket can be used to retrieve information about the job at a later point. Besides that you have access to more detailed information about the job such as status, execution time, associated schedules and more. Please refer to the documentation of the [JobManagerInterface](../Job/JobManagerInterface.php) to get an overview of the full API.

### Scheduling a job

You can also define schedules for jobs for repeated execution. To do so you have to provide the schedule as an argument when the job is

```php
/**
 * @var Abc\Bundle\JobBundle\Job\ManagerInterface
 */
$manager = $container->get('abc.job.manager');

$schedule = Abc\Bundle\SchedulerBundle\Model\Schedule('cron', '*/5 * * * *');

$job = $manager->addJob('my_job', array('Hello ' 'World!'), $schedule);
```

In this example we created a CRON schedule with a CRON specification that will execute the job every 5 minutes.


## How-Tos

- [How-To work with the manager](./docs/howto-manager.md)
- [How-To work with the job status](./docs/howto-status.md)
- [How-To modify a job at runtime](./docs/howto-modify-job.md)
- [How-To inject runtime parameters](./docs/howto-inject-runtime-parameters.md)

## Further Documentation

- [Scheduled jobs](./docs/scheduled-jobs.md)
- [Lifecycle events](./docs/lifecycle-events.md)
- [Logging](./docs/logging.md)
- [The REST-API](./docs/rest.md)
- [Configuration Reference](./docs/configuration-reference.md)