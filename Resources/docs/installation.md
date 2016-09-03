Installation
============

## Install required bundles

Make sure the following bundles are installed and configured:

* [DoctrineBundle](http://symfony.com/doc/master/bundles/DoctrineBundle/index.html)
* [JMSSerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle)
* [AbcSchedulerBundle](https://github.com/aboutcoders/scheduler-bundle)
* [AbcEnumSerializerBundle](https://github.com/aboutcoders/enum-serializer-bundle)

## Install Message Queue Bundle

The AbcJobBundle supports multiple message queue backends by providing adapters for the following bundles:

* [BernardBundle](https://github.com/bernardphp/BernardBundle)
* [SonataNotificationBundle](https://github.com/sonata-project/SonataNotificationBundle).


| Backend            | Sonata | Bernard |
|--------------------|--------|---------|
| Doctrine DBAL      |    x   |    x    |
| PhpAmqp / RabbitMQ |    x   |    x    |
| InMemory           |    x   |         |
| Predis / PhpRedis  |        |    x    |
| Amazon SQS         |        |    x    |
| Iron MQ            |        |    x    |
| Pheanstalk         |        |    x    |

Please install the bundle fr the preferred backend.
 
### Install BernardBundle

Please follow the installation instructions of the  [BernardBundle](https://github.com/bernardphp/BernardBundle).

__Note:__ If you want using Symfony 3, you have to install the [BernardBundle](https://github.com/bernardphp/BernardBundle) from the master branch. Otherwise you can use the latest stable version.
 
### Install SonataNotificationBundle

Please follow the installation instructions of the [SonataNotificationBundle](https://github.com/sonata-project/SonataNotificationBundle).

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