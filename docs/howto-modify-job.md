How-To modify a job at runtime
===================================

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

If this is the case the job will be set before each invocation of the job. This gives you the chance to e.g. change the parameters of the job, add edit or remove the schedules of the job.

__Example:__

```php
class MyJob implements JobAwareInterface {

  private $job;

  public function setJob(JobInterface $job) {
    $this.job = $job;
  }


  public function doSomething() {

    // ...

    if($someCondition && $job->hasSchedule()) {
      $job->removeSchedules();
    }
  }
}
```