How-to work with the manager
============================

## Retrieving the manager

In order to retrieve the manager you need the container:

```php
$manager = $container->get('abc.job.manager');
```

## Adding a new job

There are two ways to add a new job:

The short ways is to create and add the job in one step:

```php
$job = $manager->addJob('say_hello', array('Hello World!'));
```

The longer way is to do it on two steps. This can be useful if you e.g. want to add one or more schedules for a job.

```php
$job = $manager->create('say_hello', array('Hello World!'));

$manager->add($job);
```

## Getting a job

At some point you might want to check the status of job, check whether it was already processed or not. To do this you must have the job ticket, that you got, when the job was created.

```php
$ticket = getTicketFromSomewhere();

$job = $manager->get($ticket);
```

## Cancelling a job

You can cancel a job at anytime. At this point however jobs can only be cancelled effectively if they are not currently being processed. There are two ways to cancel a job:

Use `cancelJob` in case you only know the ticket:

```php
$manager->cancelJob($ticket);
```

Use `cancel` in case you already retrieved the job from the manager:

```php
$manager->cancel($job);
```

## Retrieving the logs

Each job can log to a dedicated log file. There are two ways to get the logs:

Use `cancelJob` in case you only know the ticket:

```php
$logsString->getJobLogs($ticket);
```

Use `getLogs` in case you already retrieved the job from the manager:

```php
$logsString->getLogs($job);
```