Logging
=======

During the execution of a job each job has access to it's own standard PSR logger.

To access the logger of a job you simply have to annotate your method and the logger will be injected.

```php
namespace My\Bundle\ExampleBundle\Job\MyJob;

class MyJob
{
    /**
     * @JobParameters({"@logger"})
     */
    public function doSomething(Psr\Log\LoggerInterface $logger)
    {
        $this->job->getLogger()->info('Hello World');
    }
}
```

## Writing log files to the database

By default the logs are written to files. This only makes sense in a development environment. In a productive environment you might want to store the logs in the database. To do so you simply have to change the handler to `orm` in the config.yaml:

´

## Changing the directory of log files

By default log files are stored in the directory %kernel.logs_dir%. If you want to store the log files in a different directory you can do so by changing the logging configuration.

```yaml
# app/config.yml
abc_job:
    log_dir: /path/to/directory
```

## Changing the default log level

You can change the default log level of all jobs within the applications config.yml file.

```yaml
# app/config.yml
abc_job:
    logging:
        default_level: debug
```

Allowed values for the log level are: debug, info, notice, warning, error, critical and alert.

## Changing the log level of a specific job

You can configure a different log level for each type of job.

```yaml
# app/config.yml
abc_job:
    logging:
        default_level: error
        levels:
            my_job: debug
```

This will set the default level to error and the level of the job "my_job" to debug.

## Changing the formatter

If you want to use a different formatter than the default one you need to register this formatter as a service within the service container. You can then specify this service within the logging configuration.

```yaml
# app/config.yml
abc_job:
    logging:
        formatter: my_formatter_id
```

## Register processors

You can register custom processors for the logger. To do so the processors need to be registered as services within the service container. You can then specify these processors you want to use within the logging configuration.

```yaml
# app/config.yml
abc_job:
    logging:
        processors:
            - my_processor_id
            - my_other_processor_id
```

## Internal logging channels

Besides the dedicated logger for jobs the bundle also uses the logger registered within the service container. To ease debugging different internal services are logging to different log channels. The following log channels are used:

- "abc.job.eraser"
- "abc.job.manager"
- "abc.job.listener.schedule"
- "abc.job.queue_engine"