How-to modify a job at runtime
===================================

In some cases it might be necessary to modify the parameters or schedule of a job at runtime for subsequent executions. This in general only makes sense if a schedule is defined for the given job.

To modify a job at runtime your job class must implement the interface [JobAwareInterface](../Job/JobAwareInterface.php).

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

If this is the case the job will be injected each time before the job is executed. With this it is possible to modify the parameters, schedules and response of the job.

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