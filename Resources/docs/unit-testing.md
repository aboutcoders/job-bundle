Unit Testing
============

The AbcJobBundle provides a class [JobTestCase](../../Test/JobTestCase.php) that can be used to test custom jobs.

So far this class provides three methods that can be used to test custom jobs.

## Testing if a job is registered

With the method `assertJobIsRegistered` you can test if a certain job is registered with the expected service and method.

```php
namespace Tests\AppBundle\Job\MyJob;

use Abc\Bundle\JobBundle\Test\JobTestCase;

class MyJobTest extends JobTestCase
{
    public function setUp()
    {
        self::bootKernel();
    }

    public function testJobIsRegistered()
    {
        $this->assertJobIsRegistered('say_hello', 'my_job', 'sayHello');
    }
}
```

## Testing if a job can be invoked

With the method `assertInvokesJob` you can test if a job can be invoked with the given parameters. To do so the given parameters are serialized/deserialized and resolved together with the runtime parameters.

```php
namespace Tests\AppBundle\Job\MyJob;

use Abc\Bundle\JobBundle\Test\JobTestCase;

class MyJobTest extends JobTestCase
{
    public function testInvokeJob()
    {
        self::bootKernel();
        
        $this->assertInvokesJob('say_hello', ['World']);
    }
}
```

## Testing parameters

The method `resolveParameters` returns the parameters a job will be invoked with, including the runtime parameters. It performs serialization/deserialization of the parameters identical to when a job is added or updated.

```php
namespace Tests\AppBundle\Job\MyJob;

use Abc\Bundle\JobBundle\Test\JobTestCase;

class MyJobTest extends JobTestCase
{
    public function testParameters()
    {
        self::bootKernel();
        
        $parameters = $this->resolveParameters('say_hello', ['World']);
        
        // $this->assert...
        // ...
    }
}
```