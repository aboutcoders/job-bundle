How-to manage other jobs at runtime
===============================

In some situations it might be necessary to manage other jobs at runtime of a given job. There are two ways to achieve this:

1. Implement the ManagerAwareInterface
2. Inject the manager as a runtime parameter

Which one of those you choose is totally up to you.

## Approach 1: Implement the ManagerAwareInterface

Your job class must implement the interface [ManagerAwareInterface](../../Job/ManagerAwareInterface.php).

```php
interface ManagerAwareInterface
{
    /**
     * @param ManagerInterface $manager
     * @return void
     */
    public function setManager(ManagerInterface $manager);
}
```

## Approach 2: Inject the manager as a runtime parameter

To inject the manager as a runtime parameter you simply have to specify the `@manager` service in the `@JobParameters` annotation of your method and the manager will be injected.

```php
namespace My\Bundle\ExampleBundle\Job\MyJob;

use Abc\Bundle\JobBundle\Job\ManagerInterface;

class MyJob
{
    /**
     * @JobParameters({"@manager"})
     */
    public function doSomething(ManagerInterface $manager)
    {
        $manager->add(...);
    }
}
```