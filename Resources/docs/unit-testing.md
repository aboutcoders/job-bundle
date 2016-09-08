Unit Testing
============

The AbcJobBundle provides a class [JobTestCase](../../Test/JobTestCase.php) that can be used to test if a job can be invoked by the AbcJobBundle as expected. This basically covers the seralization/deserialization of parameters as well as the invocation with additional [runtime parameters](./runtime-paramerters.md).


```php
namespace Tests\AppBundle\Job\MyJob;

use AppBundle\Job\MyJob;
use Abc\Bundle\JobBundle\Test\JobTestCase;

class MyJobTest extends JobTestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();
    }

    public function testJobIsRegistered()
    {
        $this->assertJobIsRegistered('my_job');
    }

    public function testClass()
    {
        $this->assertJobClass('my_job', MyJob::class);
    }

    public function testParameters()
    {
        $param = new MyParameter(...);

        $this->assertJobInvokedWithParams('my_job', [$param]);
    }
}
```

If your job uses [runtime parameters](./runtime-parameters.md) others than the default one you can simply pass parameters to method `assertJobInvokedWithParams`

```php
    public function testParameters()
    {
        $param1 = ...
        $param2 = ...
        
        $runtimeParam = ...

        $this->assertJobInvokedWithParams('my_job', [$param, $param2], ['my_runtime_param' => $runtimeParam]);
    }
```