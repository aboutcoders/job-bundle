Performance
===========

Jobs are persisted to the database by default. Than means whenever a job is added or processed database operations are performed in order to update information about the job such as the status, parameters, response etc. This can lead to a performance problem if you e.g. want to process hundreds of thousands of jobs in a short period of time.

One way to solve this is to bypass management of the job. You can do so by directly publishing a job to the queue:

```php
$manager = $container->get('abc.job.manager');

$manager->publishJob('say_hello', array('World'));
```

__Note:__ With this method no information about the job wll be persisted and thus you cannot get any information about the job at a later point. Also logging and scheduling does not work for these kind of jobs.