Validation
==========

The AbcJobBundle uses the [Symfony Validation Component](http://symfony.com/doc/current/validation.html) for validation. The validation happens inside the [job manager](./job-management.md) whenever a job is added or updated. Despite that the validator is also used inside the controllers that serve the [REST API](./rest-api.md) to validate query and request parameters.

## Validating the parameters of a job

The bundle defines a set of constraints that are used to validate a job. However jobs are invoked with parameters and these are specific for each job. In order to validate the parameters of a job you need to define a class that implements the interface [ConstraintProviderInterface](../../Validator/ConstraintProviderInterface.php) and register this class within the service container.

To be continued.