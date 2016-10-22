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

## Demo Project
Take a look at [aboutcoders/job-bundle-skeleton-app](https://github.com/aboutcoders/job-bundle-skeleton-app) to see how it can be used with Symfony project.

## Disclaimer

Please note that this bundle is still in development and thus we might need to change things including the external API if necessary. We are planning to release the first stable release the next weeks.

## Documentation

- [Installation](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/installation.md)
- [Configuration](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/configuration.md)
- [Basic Usage](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/basic-usage.md)
- [Message Consuming](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/message-consuming.md)
- [Job Management](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/job-management.md)
- [Scheduled Jobs](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/scheduled-jobs.md)
- [Cancel Jobs](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/cancel-jobs.md)
- [Runtime Parameters](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/runtime-parameters.md)
- [Serialization](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/serialization.md)
- [Validation](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/validation.md)
- [Logging](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/logging.md)
- [Unit Testing](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/unit-testing.md)
- [Process Control](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/process-control.md)
- [Lifecycle Events](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/lifecycle-events.md)
- [Clustered Environment](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/clustered-environment.md)
- [REST-API](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/rest-api.md)
- [Configuration Reference](https://github.com/aboutcoders/job-bundle/blob/master/Resources/docs/configuration-reference.md)

### Missing Features For The Stable Release
- Add consumer management

### Planned Features

- Add support for CouchDB, MongoDB
- Add support for [qpush-bundle](https://www.google.de/webhp?q=qpushbundle)
- Integrate https://github.com/beberlei/metrics
- Utilize stopwatch
