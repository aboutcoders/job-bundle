REST-API
========

The AbcJobBundle ships with a REST-API that was built based on [FOSRestBundle](https://github.com/FriendsOfSymfony/FOSRestBundle) and [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle). Please refer to the documentation of the [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle) which will provide the full documentation of the API.

## Preparing jobs for the REST-API

In case your job requires parameters to be invoked and you want to make job invocation possible using the REST-API you need to follow three steps:

1. Define a form class
2. Register the form class as service
3. Configure the formClass of your job

### Step 1: Define a form class

First you have to define the form class for your parameters. This can be any regular form class that implements the `FormTypeInterface`.

__Note:__ There is one special requirement related to this form class. The `$form->getData()` method must return an array representing the job parameters. This requires most likely that you register a data transformer that is responsible for that. Please take a look at the implementation of the class [MessageType](../Form/Type/MessageType.php) that defines the parameters of the `mailer` job.

### Step 2: Register the form class as service

Register the form class as a service and tag it with tag `form.type`;

```xml
<service id="abc.job.form.type.message" class="Abc\Bundle\JobBundle\Form\Type\MessageType" public="true">
    <tag name="form.type" />
</service>
```

### Step 3: Configure the formClass of your job

Next you need to register the form class for your job. This is done in the same place where you registered the job as a service:

```xml
<service id="abc.job.mailer" class="Abc\Bundle\JobBundle\Job\Mailer\Mailer" public="true">
    <argument type="service" id="mailer"/>
    <tag name="abc.job" type="mailer" method="send" formClass="Abc\Bundle\JobBundle\Form\Type\MessageType"/>
</service>
```