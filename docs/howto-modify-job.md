How-to modify a job at runtime
===================================

In some cases it might be necessary to modify the parameters of a job, add or remove schedules of a job or set or change the response of a job at runtime to change the behavior of subsequent executions.

In order to modify a job at runtime your job class must implement the interface [JobAwareInterface](../Job/JobAwareInterface.php).

```php
interface JobAwareInterface
{
    /**
     * @param JobInterface $job
     * @return void
     */
    public function setJob(JobInterface $job);
}
```

If this is the case the job will be set before each invocation of the job. With this you have access to the parameters, schedules and response of the job.

__Example:__

```php
class MyJob implements JobAwareInterface {

  private $job;

  public function setJob(JobInterface $job) {
    $this->job = $job;
  }


  public function doSomething() {

    // ...

    if($someCondition && $job->hasSchedule()) {
      $job->removeSchedules();
    }
  }
}
```