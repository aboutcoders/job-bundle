Configuration
=============

## Basic Configuration

### Configure backend adapter

Configure the adapter according to the message queue bundle that is used:

```yaml
# app/config/config.yml
abc_job:
    adapter: sonata
    register_default_jobs: true
```

If you want to register the default jobs shipped with this bundle you have to enable this within the configuration.

### Register a doctrine mapping type

The bundle requires to register the following doctrine mapping type:

```yaml
# app/config/config.yml
doctrine:
    dbal:
        types:
            abc.job.status: Abc\Bundle\JobBundle\Doctrine\Types\StatusType
```

### Register GDMO Timestampable

The bundle makes use of the GDMO Timestampable behavior. There are different approaches on how you can set up this behavior. Please refer to the [official symfony documentation](http://symfony.com/doc/current/cookbook/doctrine/common_extensions.html) and follow the instructions there.

### Import AbcJobBundle routing files (Optional)

If you want to enable the JSON REST-API you have to import the routing files.

```yaml
# app/config/routing.yml
abc-rest-job:
    type: rest
    resource: "@AbcJobBundle/Resources/config/routing/rest-all.yml"
    prefix: /api
```

### Update the database schema

Finally you need to update your database schema in order to create the required tables.

```bash
php bin/console doctrine:schema:update --force
```

### Create a dedicated environment (Optional)

It is recommended to run the job processing in a different environment that the production or dev environment. Please refer to the [official documentation](http://symfony.com/doc/current/configuration/environments.html) to see how this can be done.

## Advanced Configuration

### Queue Configuration

The AbcJobBundle sends and consumes messages from queues.

#### Default Queue

By default there is one queue defined with the name `default`. You can change the name if this default queue:

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
            - abc_mailer
        maintenance:
            - cleanup_database_job
```

This will define the queues `mail`, `maintenance` and the default queue `default`, where jobs of type `abc_mailer` are sent to the queue `mail`, jobs of type `cleanup_database_job` are sent to the queue `maintenance` and all others go into the `default` queue.

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

#### Saving logs in the database

By default logs of jobs are written to files. In the production environment you might want to store the logs in the database instead. To do so you simply have to change the handler to `orm`:

```yaml
# app/config/config.yml
abc_job:
    logging:
        handler: orm
```

#### Changing the directory where log files are stored

By default log files are stored in the directory `%kernel.logs_dir%`. You can change the directory where log files are stored.

```yaml
# app/config/config.yml
abc_job:
    logging:
        directory: '/path/to/directory'
```

#### Changing the default log level of all jobs

The default log level for all jobs is `info`. You can change the default level to the values `debug`, `info`, `notice`, `warning`, `error`, `critical` and `alert`.

```yaml
# app/config/config.yml
abc_job:
    logging:
        default_level: debug
```

#### Changing the log level of a specific job

You can configure a specific log level for each job.

```yaml
# app/config/config.yml
abc_job:
    logging:
        custom_level:
            my_job: debug
```

#### Register processors

You can register custom processors for the job logger. To do so the processors must be registered as services within the service container and then referenced within the configuration.

```yaml
# app/config/config.yml
abc_job:
    logging:
        processors:
            - my_processor_id
            - my_other_processor_id
```

#### Internal log channel

Besides the dedicated logger for jobs the AbcJobBundle uses the default logger registered within the container.

Besides the dedicated logger for jobs the bundle also uses the logger registered within the service container. This logger is configured to use the channel `abc.job`.