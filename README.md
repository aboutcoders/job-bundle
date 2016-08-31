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

- [Installation](./Resources/docs/01-installation.md)
- [Configuration](./Resources/docs/02-configuration.md)
- [Basic Usage](./Resources/docs/03-basic-usage.md)
- [Message Consuming](./Resources/docs/04-message-consuming.md)
- [Job Management](./Resources/docs/05-job-management.md)
- [Scheduled Jobs](./Resources/docs/06-scheduled-jobs.md)
- [Cancel Jobs](./Resources/docs/07-cancel-jobs.md)
- [Runtime Parameters](./Resources/docs/08-runtime-parameters.md)
- [Logging](./Resources/docs/09-logging.md)
- [Lifecycle Events](./Resources/docs/10-lifecycle-events.md)
- [REST-API](./Resources/docs/11-rest-api.md)
- [Configuration Reference](./Resources/docs/12-configuration-reference.md)

### Mising Features For The Stable Release
- Provide a TestCase for jobs
- Get rid of forms to serialize/desiralize jobs in controllers
- Add option to validate parameters inside job manager
- Add option to force cancellation of a job
- Add option to register custom handlers for job logs

### Planned Features
- Cleanup & document agent management
- Consider consumer management (start, stop, pause)
- Utilize stopwatch
- Monitoring
- Support for [qpush-bundle](https://www.google.de/webhp?q=qpushbundle)