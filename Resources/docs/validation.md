Validation
==========

The AbcJobBundle uses the [Symfony Validation Component](http://symfony.com/doc/current/validation.html). Validation happens inside the [job manager](./job-management.md) whenever a job is added or updated. Besides that the validator is also used inside the controllers that provide the [REST API](./rest-api.md) in order to validate query parameters.

The bundle defines a set of constraints to validate a job. However jobs are invoked with parameters and these are specific for every job. Job parameters that are classes are validated implicitly provided that constraints are defined for this class. In order to validate other types of parameters such as numbers, strings or arrays or in order to define that a parameters is required you have to define a constraint provider.

## Creating a Constraint Provider

A constraint provider needs to implement the  interface [ConstraintProviderInterface](../../Validator/ConstraintProviderInterface.php). The constraint provider gives you an opportunity to define validation constraints for the parameters of custom jobs.

```php
namespace AppBundle\Validation\Job;

use Abc\Bundle\JobBundle\Validator\Job\ConstraintProviderInterface;

class CustomConstraintProvider implements ConstraintProviderInterface
{
    public function getPriority()
    {
        // ...
    }
    
    public function getConstraints($type)
    {
        // ...
    }
```

The constraint provider must define the two methods `getPriority` and `getConstraints`. The method `getPriority` expects a number as return value. This number is used to determine which constraints are used in case multiple constraint providers are registered for the same job type. The method `getConstraints` passes the job type as argument and expects an array of constraints as return value.

The best way to understand this is see it in action. Assuming we have the following job:

```php
class CustomJob
{
    /**
     * @ParamType("value1", type="string")
     * @ParamType("value2", type="string")
     */
    public function execute($value1, $value2)
    {
        // ...
    }
```

This job defines two parameters `$value1` and `$value1`. A constraint provider for this job could be:

```php
namespace AppBundle\Validation\Job;

use Abc\Bundle\JobBundle\Validator\Job\AbstractConstraintProvider;
use Symfony\Component\Validator\Constraints as Assert;

class CustomConstraintProvider extends AbstractConstraintProvider
{
    public function getConstraints($type)
    {
        if ('custom_job' == $type) {
           return array(
               new Assert\Choice(array('choices' => array('foo', 'bar'))),
               new Assert\Range(array('min' => 1))
           ); 
        }
    }
```

This would defined the constraint `Assert\Choice` for the parameter `$value1` and the constraint `Assert\Range` for the parameters `$value2`.

You can also define a multiple constraints for one parameters:

```php
class CustomConstraintProvider extends AbstractConstraintProvider
{
    public function getConstraints($type)
    {
        if ('custom_job' == $type) {
           return array(
               array(
                   new Assert\NotBlank(),
                   new Assert\Choice(array('choices' => array('foo', 'bar'))),
               ),
               array(
                   new Assert\NotBlank(),
                   new Assert\Range(array('min' => 1))
               )
           ); 
        }
    }
```

As you can see, the number of elements returned in the array matches the number of parameters of the job. Each array element can be a single constraint or an array of constraints. The previous example adds the constraint that both parameters `$value1` and `$value2` cannot be empty.

Please also note that the constraint provider inherits from `AbstractConstraintProvider`. This provider defines a default priority of `-1`;

## Registering a Constraint Provider

You need to register your constraint provider within the container. The constraints will then be registered for the according job types.

```yml
# app/config/services.yml
services:
    app.job_constraint_provider:
        class: AppBundle\Valdation\Job\CustomConstraintProvider
        tags:
            - { name: abc.job.constraint_provider }
```

## Validating Jobs with Runtime Parameters

The [runtime parameters](./runtime-parameters.md) of jobs are not validated. That means if you want to validate the parameters of a job that defines [runtime parameters](./runtime-parameters.md) you simply ignore them in the returned array of of constraints.
 
```php
class CustomJob
{
    /**
     * @ParamType("type", type="string")
     * @ParamType("logger", type="@abc.logger")
     * @ParamType("number", type="intger")
     */
    public function execute($type, $logger  $number)
    {
        // ...
    }
```

The job requires three parameters where the second is a [runtime parameter](./runtime-parameters.md). The constraint provider for this job must return an array with no more than, two elements:

```php
class CustomConstraintProvider extends AbstractConstraintProvider
{
    public function getConstraints($type)
    {
        if ('custom_job' == $type) {
           return array(
               new Assert\Choice(array('choices' => array('foo', 'bar'))),
               new Assert\Range(array('min' => 1))
           ); 
        }
    }
```

## Skipping validating of single parameters

In order to skip validation of single parameters the constraint provider must simple return a null for this according index.

```php
class CustomConstraintProvider extends AbstractConstraintProvider
{
    public function getConstraints($type)
    {
        if ('custom_job' == $type) {
           return array(
               null,
               new Assert\Range(array('min' => 1))
           ); 
        }
    }
```

## Configuration

### Disable validation inside the job manager

Validation inside the [job manager](./job-management.md) is enabled by default. Although not recommended you can disable validation inside the manager.
 
```yaml
# app/config/config.yml
abc_job:
    manager:
        validate: false
```

### Enable validation inside controllers

You can disable validation within the [job manager](./job-management.md) and enable it inside the controllers instead. This way jobs are validated if added or updated over the REST API whereas they are not validated else wise.

```yaml
# app/config/config.yml
abc_job:
    manager:
        validate: false
    rest:
        validate: true
```