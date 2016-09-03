Process Control
===============

The AbcJobBundle integrates the [process control](https://github.com/aboutcoders/process-control) library and thereby makes it possible to inform jobs about external events such as process termination signals or that a job has been [cancelled by the user](./cancel-jobs.md). 

By default jobs are only informed if they have been [cancelled manually](./cancel-jobs.md) by the user. In order to also inform jobs about process signals sent to the long running [consumer command](./message-consuming.md) you have to install the [AbcProcessControlBundle](https://github.com/aboutcoders/process-control-bundle).

## Process Control and SonataNotificationBundle

If you are using the AbcJobBundle together with the [SonataNotificationBundle](https://github.com/sonata-project/SonataNotificationBundle) as message queue backend we recommend to also install the [AbcNotificationBundle](https://github.com/aboutcoders/notification-bundle). This bundle inherits from the [SonataNotificationBundle](https://github.com/sonata-project/SonataNotificationBundle) and integrates process control more deeply into [SonataNotificationBundle](https://github.com/sonata-project/SonataNotificationBundle) and thereby allows an even better control of [message consuming](./message-consuming.md).