Scheduled Jobs
==============

You can configure one or more schedules for a job in order to configure repeated execution of a job. The bundle relies on the [AbcSchedulerBundle](https://github.org/aboutcoders/scheduler-bundle) to provide this functionality.

## Creating a schedule

There are different ways to create a schedule. One way is to use the `createSchedule` method of the job.

```php
$job = $manager->create('foobar');

$schedule = $job->createSchedule('cron', '* * * * *');
```

You can also create a new instance

```php
$schedule = Abc\Bundle\SchedulerBundle\Model\Schedule('cron', '*/5 * * * *');
```

You can also define your own Schedule class if you think this is necessary. You only need to make sure that schedule implements the interface [ScheduleInterface](https://github.com/aboutcoders/scheduler-bundle/blob/master/Model/ScheduleInterface.php) defined by the [AbcSchedulerBundle](https://github.org/aboutcoders/scheduler-bundle).

## Adding a scheduled job

There are different ways to add a scheduled job. In case you job only needs one schedule this is the shortest way:

```php
$job = $manager->addJob('my_job', array('Hello ' 'World!'), $schedule);
```

You can also create schedules first and then add them to the job and finally add the job to the manager:

```php
$job = $manager->create('foobar');

$job->addSchedule($schedule1);

$job->addSchedule($schedule2);

$job = $manager->add($job);
```

## Removing a schedule

Existing schedules can also be removed:

```php
$job->removeSchedule($schedule);
```

If you want to remove the schedule as part of the job execution you have to [inject the job manager into the job](./runtime-parameters.md).