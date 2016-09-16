Configuration
=============

## Basic Configuration

### Configure backend adapter

Configure the adapter according to the message queue bundle you are using:

```yaml
# app/config/config.yml
abc_job:
    adapter: sonata
    register_default_jobs: true
```

If you want to register the default jobs shipped with this bundle you have to enable this within the configuration.

### Register a doctrine mapping type

The bundle requires registration of the following custom doctrine mapping type:

```yaml
# app/config/config.yml
doctrine:
    dbal:
        types:
            abc.job.status: Abc\Bundle\JobBundle\Doctrine\Types\StatusType
```

### Import AbcJobBundle routing files (Optional)

If you want to enable the JSON REST-API you have to import the routing files.

```yaml
# app/config/routing.yml
abc-rest-job:
    type: rest
    resource: "@AbcJobBundle/Resources/config/routing/rest-all.xml"
    prefix: /api
```

### Update the database schema

Finally you need to update your database schema in order to create the required tables.

```bash
php bin/console doctrine:schema:update --force
```

### Create a dedicated environment (Optional)

It is recommended to run the job processing in a different environment than the default environments.

Since this environment is by symfony commands all you need to do is a `config_ENV.yml` (e.g. `config_job-processing.yml`) file where `ENV` equals the name of the environment. 

Please refer to the [official documentation](http://symfony.com/doc/current/configuration/environments.html) if you need more information.

## Advanced Configuration

### Queue Configuration

The AbcJobBundle sends and consumes messages from queues.

#### Default Queue

By default there is one queue defined with the name `default`, you can change the name of this queue:

```yaml
# app/config/config.yml
abc_job:
    adapter: sonata
    default_queue: my_queue
```

#### Multiple Queues

You can define more queues and assign jobs to each queue:

```yaml
# app/config/config.yml
abc_job:
    adapter: sonata
    queues:
        mail:
            - abc.mailer
        maintenance:
            - cleanup_database_job
```

This will define the queues `mail`, `maintenance` and the default queue `default`, where jobs of type `abc.mailer` are sent to the queue `mail`, jobs of type `cleanup_database_job` are sent to the queue `maintenance` and all others go into the `default` queue.

__Note:__ If you decided to use the `sonata` adapter you still have to configure the queues within the Sonata configuration as explained in the [documentation](https://sonata-project.org/bundles/notification/master/doc/reference/multiple_queues.html).

Referring to the previous example the queues configuration for the SonataNotificationBundle must be:

```yaml
# app/config/config.yml
sonata_notification:
    queues:
        - { queue: default, default: true }
        - { queue: mail, types: [abc_mailer]}
        - { queue: maintenance, types: [cleanup_database_job]}
        
```

### Logging

The bundle provides a dedicated logger for each job using the [monolog library](http://symfony.com/doc/current/logging.html). Whenever a job is executed a dedicated handler referred to as `storage_handler` is created and [injected into the job](./logging.md). This handler preserves the original data structure of log records. 

#### Storage Handler Configuration

You can choose whether to store logs on the filesystem or database, change the default log level, or assign custom processors:

```yaml
# app/config/config.yml
abc_job:
    logging:
        storage_handler:
            type: file              # Choose "orm" if you want to store job logs in the database instead of files
            path: %kernel.logs_dir% # The directory where the json encoded logs are stored (ignored if handler is "orm")
            level: info             # The default log level
            bubble: false           # Whether the messages that are handled can bubble up the stack or not
            processor:
                - my_processor_id   # An array of service ids of additional processors to register
```

#### Stream Handler Configuration

Besides the storage handler a regular stream handler can be enabled, that creates an additional log file for every job using the standard [LineFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/LineFormatter.php).

```yaml
# app/config/config.yml
abc_job:
    logging:
        stream_handler:             # If defined a standard stream handler will be registered
            path: %kernel.logs_dir% # The directory where log files are stored
            level: ERROR            # The minimum logging level
            bubble: false           # Whether the messages that are handled can bubble up the stack or not
            formatter: ~            # Set a different formatter than LineFormattter
            processor:
                - my_processor_id   # An array of service ids of additional processors to register
```

#### Registering Custom Handlers

Besides the storage and log handler you can define additional custom handlers that will be pushed to logger that is injected into the jobs.

```yaml
# app/config/config.yml
abc_job:
    logging:
        handler:
            - my_handler_id         # An array of service ids of additional handlers to register
```

#### Configuring a custom log level for a job

You can overwrite the default log level and specify a custom log level for each job.

```yaml
# app/config/config.yml
abc_job:
    logging:
        level:
            my_job: debug
```

#### Internal log channel

Besides the dedicated logger for jobs the AbcJobBundle uses the default logger registered within the service container. This logger is configured to use the channel `abc.job`.

Next Step: [Basic Usage](./basic-usage.md)