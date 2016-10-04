Job Management
==============

## Basic Commands

The API of the manager is defined by the interface [ManagerInterface](../../Job/ManagerInterface.php).

### Retrieving the manager

You can retrieve the manager from the service container with the following command:

```php
$manager = $container->get('abc.job.manager');
```

### Adding a new job

There are two ways to add a new job:

The short ways is to create and add the job in one step:

```php
$job = $manager->addJob('say_hello', array('World'));
```

The other way is to first create the job and then add it, this is necessary if you want to configure more than one schedule to the job. The recommended way to create a job is using the `JobBuilder`.

```php
use Abc\Bundle\JobBundle\Job\JobBuilder;

$job = JobBuilder::create('my_job')
    ->addSchedule('cron', '1 * * * *')
    ->addSchedule('cron', '30 * * * *')
    ->build();
    

$ticket = $manager->add($job)->getTicket();
```

Please note that the method `add` returns the instance of the job. This is most likely not the same instance as the one that was passed as parameter to the method. The returned job provides information such as the ticket or status information about the job.

### Getting a job

After a job was added you can get information about it with the following command:

```php
$ticket = getTicketFromSomewhere();

$job = $manager->get($ticket);
```

### Cancelling a job

Use the following command to get to cancel a job:

```php
$manager->cancel($job->getTicket());
```

### Restarting a job

Use the following command to get to restart a job:

```php
$manager->restart($job->getTicket());
```

### Updating a job

Use the following command to get to update a job:

```php
// modify the job
$job->removeSchedules()

$manager->update($job);
```

### Getting the logs of a job

Use the following command to get to logs of a job:

```php
$records = $manager->getLogs($job->getTicket());
```

The method returns an array of log records that have the very structure as the ones that are handled by a `Monolog\Handler\HandlerInterface`.

## Managing a job at runtime

In some situations it might be necessary to manage a job or other jobs at runtime of the job. There are two ways to achieve that:

1. Implement the ManagerAwareInterface
2. Inject the manager as a runtime parameter

Which one of those you choose is up to your preference.

### Implement the ManagerAwareInterface

Your job class must implement the interface [ManagerAwareInterface](../../Job/ManagerAwareInterface.php).

```php
interface ManagerAwareInterface
{
    /**
     * @param ManagerInterface $manager
     * @return void
     */
    public function setManager(ManagerInterface $manager);
}
```

### Inject the manager as a runtime parameter

To inject the manager as a runtime parameter you simply have to specify the `@abc.manager` service in the `@ParamType` annotation of your method and the manager will be injected.

```php
namespace My\Bundle\ExampleBundle\Job\MyJob;

use Abc\Bundle\JobBundle\Job\ManagerInterface;

class MyJob
{
    /**
     * @ParamType("manager", type="@abc.manager")
     */
    public function doSomething(ManagerInterface $manager)
    {
        $manager->add(...);
    }
}
```

## Working with the job status

The status of a job is defined as an enumeration (based on [myclabs/php-enum](https://github.com/myclabs/php-enum)) with the following values:

```php
const REQUESTED  = 'REQUESTED';
const PROCESSING = 'PROCESSING';
const PROCESSED  = 'PROCESSED';
const CANCELLING = 'CANCELLING';
const CANCELLED  = 'CANCELLED';
const ERROR      = 'ERROR';
const SLEEPING   = 'SLEEPING';
```

## Getting the status:

```php
$status = $manager->get($ticket)->getStatus();
```

## Status checks:

Status checks can be done like this:

```php
$status = $manager->getStatus($ticket));

if($status == Status::PROCESSED())
{
    // ...
}
```

Alternatively you can use the method ´equals´ of the Status class.

```php
$status = $manager->getStatus($ticket));

if(Status::equals(Status::PROCESSED(), $status))
{
    // ...
}
```