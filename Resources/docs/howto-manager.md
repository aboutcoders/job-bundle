How-to work with the manager
============================

The API of the manager is defined by the interface [ManagerInterface](../Job/ManagerInterface.php).

## Retrieving the manager

You can retrieve the manager from the service container with the following command:

```php
$manager = $container->get('abc.job.manager');
```

## Adding a new job

There are two ways to add a new job:

The short ways is to create and add the job in one step:

```php
$job = $manager->addJob('say_hello', array('World'));
```

The other way is to first create the job and then add it. This can be useful if you e.g. want to add one or more schedules for a job.

```php
$job = $manager->create('say_hello', array('World'));

// do something with the job

$job = $manager->add($job);

$ticket = $job->getTicket();
```

## Getting a job

After a job was added you can get information about it with the following command:

```php
$ticket = getTicketFromSomewhere();

$job = $manager->get($ticket);
```

## Cancelling a job

You can cancel a job with one of the following commands:

Use `cancelJob` in case you only know the ticket:

```php
$manager->cancelJob($ticket);
```

Use `cancel` in case you already retrieved the job from the manager:

```php
$manager->cancel($job);
```

## Retrieving the logs

Each job has access its own logger. You can retrieve the logs of a job with one of the following commands:

Use `getJobLogs` in case you only know the ticket:

```php
$logsString = $manager->getLogs($job);
```

Use `getLogs` in case you already retrieved the job from the manager:

```php
$logsString = $manager->getLogs($job);
```