# AbcJobBundle

A symfony bundle for asynchronous distributed job processing using [php-enqueue](https://github.com/php-enqueue/enqueue-dev) as transport layer.

**Note: This project is still in an experimental phase!**

## Requirements:
* PHP >= 7.2

## Features

This bundle provides the following features:

- Asynchronous distributed processing of Job (single job), Batch (multiple jobs processed in parallel), and Sequence (multiple jobs processed in sequential order)
- Status information about jobs
- Process, cancel, restart jobs.
- Scheduled processing of jobs with based on cron expressions (requires AbcSchedulerBundle) 
- JSON REST-Api & PHP client library
- [OpenApi](https://www.openapis.org/) documentation

## Demo Project

Please take a look at [job-docker-compose](https://gitlab.com/hasc/job-docker-compose) to see how the AbcJobBundle can be used within Symfony project.

## Getting Started

**Prerequisites**
* Configure a Symfony application with AbcJobBundle
* Configure the enqueue transport layer

1. In case you configured a transport with a key different that `default` you have to configure this transport also for the AbcJobBundle

	```yaml
	abc_job:
	    transport: my_transport_name
	```

2. Create database and database schema

	```bash
	bin/console doctrine:database:create
	bin/console doctrine:schema:create
	```

3. Setup the broker

	```bash
	bin/console abc:setup-broker -vvv
	```

4. Start the command that processes replies from workers

	```bash
	bin/console enqueue:transport:consume job_reply abc.reply -vvv
	```

5. Start the worker processes

	see AbcWorkerBundle

## Configuration Reference

```yaml
abc_job:
    # the enqueue transport name
    transport: default
    
    # the name of the queue jobs are sent to by default
    default_queue: default
    
    # the name of the queue where replies of jobs are sent to by default
    default_replyTo: reply
    
    # a prefix to be used for queue names
    prefix: abc
    
    # a separator for used for queue names
    separator: .
    
    # whether to enable the scheduler component
    scheduler:
        enabled: true
```

## License

The MIT License (MIT). Please see [License File](./LICENSE) for more information.
