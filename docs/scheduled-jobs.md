Scheduled Jobs
==============

You can configure one or more schedules for a job in order to configure repeated execution of a job. The bundle relies on the [scheduler-bundle](https://github.org/aboutcoders/scheduler-bundle) to provide this functionality.

## Creating a schedule

There are different ways to create a job. One way is to create it using the job:

```php
$job = $manager->create('foobar');

$schedule = $job->createSchedule('cron', '* * * * *');
```

You can also create a new instance

```php
$schedule = Abc\Bundle\SchedulerBundle\Model\Schedule('cron', '*/5 * * * *');
```

## Adding a scheduled job

There are different ways to add a scheduled job. In case you job only needs one schedule you can add the schedule directly with the job:

```php
$job = $manager->addJob('my_job', array('Hello ' 'World!'), $schedule);
```

You can also first add schedules to the job and then add the job to the manager:

```php
$job = $manager->create('foobar');

$job->addSchedule($schedule1);

$job->addSchedule($schedule2);

$job = $manager->add($job);
```

## Removing a schedule

Sometimes you might want to add a job that performs a certain check periodically and then if some condition is met, the job should terminate. To achieve this you can remove the schedule of the job:

```php
$job->removeSchedule($schedule);
```

You most likely want to do this during execution of the job which requires that your job implements the [JobAwareInterface](../Job/JobAwareInterface.php). Please refer to the article [How-To work modify a job at runtime](./howto-modify-job.md) to do so.