Scheduled Jobs
==============

You can define one or more schedules for a job in order to configure repeated execution of a job. The bundle relies on the [AbcSchedulerBundle](https://github.org/aboutcoders/scheduler-bundle) to provide this functionality.

## Creating a job with schedules

If you want to create a job with one or more schedules the recommended way is to use the `JobBuilder`:

```php
use Abc\Bundle\JobBundle\Job\JobBuilder;

$job = JobBuilder::create('my_job')
    ->addSchedule('cron', '1 * * * *')
    ->addSchedule('cron', '30 * * * *')
    ->build();
```

## Creating a schedule

If you want to create a schedule the recommended way is to use the `ScheduleBuilder`:

```php
use Abc\Bundle\JobBundle\Job\ScheduleBuilder;

$schedule = ScheduleBuilder::create('cron', '1 * * * *');
```

## Removing a schedule

You can remove previously added schedules from a job:

```php
$job->removeSchedule($schedule);
```

If you want to add or remove schedules during the execution of the job please refer to the section [Managing a job at runtime](./job-management.md).

Back to [index](../../README.md)