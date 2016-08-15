Scheduled Jobs
==============

You can configure one or more schedules for a job in order to configure repeated execution of a job. The bundle relies on the [AbcSchedulerBundle](https://github.org/aboutcoders/scheduler-bundle) to provide this functionality.

## Creating a schedule

There are different ways to create a scheduled. One way is to use the `createSchedule` method of the job.

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

In some cases it can be necessary to remove a job that was previously configured:

```php
$job->removeSchedule($schedule);
```

You most likely want to do this during execution of the job which requires that your job implements the [JobAwareInterface](../Job/JobAwareInterface.php). Please refer to the [How-To work modify a job at runtime](./howto-modify-job.md) for more details.