Configuration
=============

## Configure backend adapter

Configure the adapter according to the message queue bundle that you installed:

```yaml
abc_job:
  adapter: sonata
  register_default_jobs: true
```

If you want to register the default jobs shipped with this bundle you have to enable this within the configuration.

## Register a doctrine mapping type

The bundle requires to register the following doctrine mapping type:

```yaml
doctrine:
    dbal:
        types:
            abc.job.status: Abc\Bundle\JobBundle\Doctrine\Types\StatusType
```

## Register GDMO Timestampable

The bundle makes use of the GDMO Timestampable behavior. There are different approaches on how you can set up this behavior. Please refer to the [official symfony documentation](http://symfony.com/doc/current/cookbook/doctrine/common_extensions.html) and follow the instructions there.

## Import AbcJobBundle routing files (Optional)

If you want to enable the JSON REST-API you have to import the routing files.

```yaml
abc-rest-job:
    type: rest
    resource: "@AbcJobBundle/Resources/config/routing/rest-all.yml"
    prefix: /api
```

## Update the database schema

Finally you need to update your database schema in order to create the required tables.

```bash
php bin/console doctrine:schema:update --force
```

## Check swiftmailer configuration

If you are using the mailer job that is shipped with this bundle you have to make sure that the spool configuration is changed so that mails are sent at once and not only if the kernel terminates, which is the default behavior (see: [How to Spool Emails](http://symfony.com/doc/current/email/spool.html)).

To change this configuration only for the job processing and leave the default configuration as is you have to run the job processing in a dedicated environment (see: [How to Master and Create new Environments](http://symfony.com/doc/current/configuration/environments.html))).