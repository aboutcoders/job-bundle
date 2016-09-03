AbcJobBundle
============

A symfony bundle to process methods asynchronously as jobs by simply annotating the method and registering the class as a service within the service container.

Build Status: [![Build Status](https://travis-ci.org/aboutcoders/job-bundle.svg?branch=master)](https://travis-ci.org/aboutcoders/job-bundle)

## Features

This bundle provides the following features:

- Asynchronous execution of jobs
- Cancel, Update, Restart jobs
- Repeated execution of jobs with schedules
- JSON REST-Api
- Supported message queue backends:
  - Doctrine DBAL
  - PhpAmqp / RabbitMQ
  - InMemory
  - Predis / PhpRedis
  - Amazon SQS
  - Iron MQ
  - Pheanstalk

## Disclaimer

Please note that this bundle is still in development and thus we might need to change things including the external API if necessary. We are planning to release the first stable release the next weeks.

## Documentation

- [Installation](./Resources/docs/installation.md)
- [Configuration](./Resources/docs/configuration.md)
- [Basic Usage](./Resources/docs/basic-usage.md)
- [Message Consuming](./Resources/docs/message-consuming.md)
- [Job Management](./Resources/docs/job-management.md)
- [Scheduled Jobs](./Resources/docs/scheduled-jobs.md)
- [Cancel Jobs](./Resources/docs/cancel-jobs.md)
- [Runtime Parameters](./Resources/docs/runtime-parameters.md)
- [Logging](./Resources/docs/logging.md)
- [Lifecycle Events](./Resources/docs/lifecycle-events.md)
- [REST-API](./Resources/docs/rest-api.md)
- [Configuration Reference](./Resources/docs/configuration-reference.md)

### Mising Features For The Stable Release

- Provide a TestCase for jobs
- Add option to validate parameters inside job manager (define add-validation-group, update-validation-group)
- Make FOSRestBundle optional
- Add option to force cancellation of a job
- Add option to register custom handlers for job logs
- Add option to configure entity class names

### Planned Features

- Cleanup & document agent management
- Consider consumer management (start, stop, pause)
- Utilize stopwatch
- Monitoring
- Support for [qpush-bundle](https://www.google.de/webhp?q=qpushbundle)