REST-API
========

The AbcJobBundle ships with a REST-API that was built based on [FOSRestBundle](https://github.com/FriendsOfSymfony/FOSRestBundle) and [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle). Please refer to the documentation of the [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle) which provides the full documentation of the API.

__Note:__ The REST-API is still under development and thus in an "experimental" state. We will try to finalize it as soon as possible.

__Note:__ We also plan to provide the controllers that serve the HTML pages as soon as possible.

## Preparing jobs for the REST-API

In case want to be able to add a job from the REST-API you need to define the form that maps the input elements to the parameters that are passed to the job. To do this you need to do three things:

1. Define the form class
2. Register the form class as service
3. Configure the formClass in the service definition of the job

### Step 1: Define a form class

First you have to define the form class that maps the parameters of the job. This can be any regular form class that implements the `FormTypeInterface`.

__Note:__ There is one special requirement related to this form class. The `$form->getData()` method must return an array representing the job parameters. This requires most likely that you register a data transformer that transforms the input data into an array. Please take a look at the implementation of the class [MessageType](../Form/Type/MessageType.php) that defines the parameters of the `mailer` job that is shipped with this bundle.

### Step 2: Register the form class in the service container

Register the form class as a service in the service container and tag it with tag `form.type`;

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

__Note:__ In case your job does not require any parameters you do not need to provide a form class for it.