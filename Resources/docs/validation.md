Validation
==========

The AbcJobBundle uses the [Symfony Validation Component](http://symfony.com/doc/current/validation.html). Validation happens inside the [job manager](./job-management.md) whenever a job is added or updated. Besides that the validator is also used inside the controllers that provide the [REST API](./rest-api.md) in order to validate query parameters.

The bundle defines a set of constraints to validate a job. However jobs are invoked with parameters and these are specific for every job. Job parameters that are classes are validates implicitly provided that constraints are defined for this class. In order to validate other types of parameters such as numbers or strings or in order to define that a parameters is required you have to define a constraint provider.

## Creating a Constraint Provider

To create a constraint provider it needs to implement the  interface [ConstraintProviderInterface](../../Validator/ConstraintProviderInterface.php). The constraint provider gives you an opportunity to define validation constraints for the parameters of a custom job.

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

The constraint provider must define the two methods `getPriority` and `getConstraints`. The method `getPriority` expects a number as return value. This number is used to determine which constraints are used in case multiple providers are registered for the same job type. The method `getConstraints` passes the job type as argument and expects an array of constraints as return value.

The best way to understand this is see it in action. Assuming we have a the following job

```php
class CustomJob
{
    /**
     * @ParamType({"string","integer"})
     */
    public function execute($type, $number)
    {
        // ...
    }
```

This job defines two parameters `$type` and `$number`. A constraint provider for this job could be

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

This would defined the constraint `choice` for the parameter `$type` and the constraint `range` for the parameters `$number`.

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

With this you would define that both parameters must be set.

## Registering a Constraint Provider

You need to register your constraint provider within the container. The constraints will then be registered for the job types.

```yml
# app/config/services.yml
services:
    app.job_constraint_provider:
        class: AppBundle\Valdation\Job\CustomConstraintProvider
        tags:
            - { name: abc.job.constraint_provider }
```
[job manager](./job-management.md)
### Validating Jobs with Runtime Parameters

[Runtime parameters](./runtime-parameters.md) are not validated. That means if a job defines [runtime parameters](./runtime-parameters.md) they are simply ignored when the parameters of a job a validated.
 
```php
class CustomJob
{
    /**
     * @ParamType({"string", "@abc.logger", "integer"})
     */
    public function execute($type, $logger  $number)
    {
        // ...
    }
```

The job requires three parameters where the second is a [runtime parameters](./runtime-parameters.md). A constraint provider for this job may only define constraints for two parameters, the first and the last one:

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

## Configuration

Validation inside the [job manager](./job-management.md) is enabled by default. Although not recommended you can disable validation.
 
```yaml
# app/config/config.yml
    manager:
        validate: false
```

You can disable validation within the [job manager](./job-management.md) and enable it inside the controllers instead. This way jobs are validated if added or updated over the REST API whereas they are not validated else wise.

```yaml
# app/config/config.yml
    manager:
        validate: false
    rest:
        validate: true
```