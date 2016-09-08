AbcJobBundle
============

A symfony bundle to process jobs asynchronously by simply annotating a method and registering the class within the service container.

Build Status: [![Build Status](https://travis-ci.org/aboutcoders/job-bundle.svg?branch=master)](https://travis-ci.org/aboutcoders/job-bundle)

## Features

This bundle provides the following features:

- Asynchronous execution of jobs
- Status information about jobs
- Functionality to cancel, update, restart a job
- Repeated execution of jobs with schedules (cron based expressions)
- JSON REST-Api
- Support for multiple message queue systems:
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
- [Process Control](./Resources/docs/process-control.md)
- [Lifecycle Events](./Resources/docs/lifecycle-events.md)
- [Clustered Environment](./Resources/docs/clustered-environment.md)
- [Unit Testing](./Resources/docs/unit-testing.md)
- [REST-API](./Resources/docs/rest-api.md)
- [Configuration Reference](./Resources/docs/configuration-reference.md)

### Missing Features For The Stable Release

- Add validator (define add-validation-group, update-validation-group)
- Add consumer management

### Planned Features

- Add support for CouchDB, MongoDB
- Add support for [qpush-bundle](https://www.google.de/webhp?q=qpushbundle)
- Integrate https://github.com/beberlei/metrics
- Utilize stopwatch