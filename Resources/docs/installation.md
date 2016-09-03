Installation
============

## Install required bundles

Make sure the following bundles are installed and configured:

* [DoctrineBundle](http://symfony.com/doc/master/bundles/DoctrineBundle/index.html)
* [JMSSerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle)
* [AbcSchedulerBundle](https://github.com/aboutcoders/scheduler-bundle)
* [AbcEnumSerializerBundle](https://github.com/aboutcoders/enum-serializer-bundle)

## Install Message Queue Bundle

The AbcJobBundle supports the following message queue backends by integrating with the following two bundles:
 
* [BernardBundle](https://github.com/bernardphp/BernardBundle) (Experimental)
* [SonataNotificationBundle](https://github.com/sonata-project/SonataNotificationBundle)

| Backend            | Sonata | Bernard |
|--------------------|--------|---------|
| Doctrine DBAL      |    x   |    x    |
| PhpAmqp / RabbitMQ |    x   |    x    |
| InMemory           |    x   |         |
| Predis / PhpRedis  |        |    x    |
| Amazon SQS         |        |    x    |
| Iron MQ            |        |    x    |
| Pheanstalk         |        |    x    |

Please choose the preferred backend and install the corresponding bundle.
 
### BernardBundle

The stable version of the [BernardBundle](https://github.com/bernardphp/BernardBundle) does not support Symfony 3 yet. If you want to setup the bundle in a Symfony 3 project you have to install the BernardBundle using the master branch.
 
### SonataNotificationBundle
 
In case you are decided to use [SonataNotificationBundle](https://github.com/sonata-project/SonataNotificationBundle) please install the following additional bundle:
 
* [AbcNotificationBundle](https://github.com/aboutcoders/notification-bundle)

The [AbcNotificationBundle](https://github.com/aboutcoders/notification-bundle) inherits from the [SonataNotificationBundle](https://github.com/sonata-project/SonataNotificationBundle) to integrate [process control](https://github.com/aboutcoders/process-control-bundle) and thereby support starting and stopping the queue processing in a controlled way. This is especially helpful in a continuous deployment setup.

## Install AbcJobBundle

Download the bundle using composer:

```
$ composer require "aboutcoders/job-bundle:dev-master"
```

Include the bundle in the AppKernel.php class

```php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Abc\Bundle\JobBundle\AbcJobBundle(),
    );

    return $bundles;
}
```

## Install REST Bundles (Optional)

If you want to use the REST-API make sure the following additional bundles are installed and configured:

* [SensioFrameworkExtraBundle](http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle)
* [FOSRestBundle](https://github.com/FriendsOfSymfony/FOSRestBundle)
* [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle)