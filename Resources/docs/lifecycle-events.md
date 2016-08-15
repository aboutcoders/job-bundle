Lifecycle Events
================

During the lifecycle of a job events are dispatched using the [Symfony Event Dispatcher](http://symfony.com/doc/current/components/event_dispatcher/index.html).

### Pre Execute Event

Before every execution of a job an event with the name `abc.job.pre_execute` is dispatched.

In order to register an event listener for this event you have to define the listener class, register it as a service and tag it:

```yml
# app/config/config.yml
services:
    acme.listener.your_listener_name:
        class: Acme\Bundle\MyBundle\Listener\MyJobListener
        tags:
            - { name: abc.job.event_listener, event: abc.job.pre_execute, method: onPreExecute }
```

The dispatched event is of type [ExecutionEvent](../../Event/ExecutionEvent.php) which gives listeners the option to modify the job or to [provide runtime parameters](./howto-inject-runtime-parameters.md).

### Post Execute Event

After every execution of a job an event with the name `abc.job.pre_execute` is dispatched.

In order to register an event listener for this event you have to define the listener class, register it as a service and tag it:

```yml
# app/config/config.yml
services:
    acme.listener.your_listener_name:
        class: Acme\Bundle\MyBundle\Listener\MyJobListener
        tags:
            - { name: abc.job.event_listener, event: abc.job.post_execute, method: onPostExecute }
```

Like the Pre Execution Event the dispatched event is of type [ExecutionEvent](../../Event/ExecutionEvent.php).


### Terminate Event

Whenever a job terminates an event with the name `abc.job.terminated` and of type [TerminationEvent](../../Event/TerminationEvent.php) is dispatched.

In order to register an event listener for this event you have to define the listener class, register it as a service and tag it:

```yml
# app/config/config.yml
services:
    acme.listener.your_listener_name:
        class: Acme\Bundle\MyBundle\Listener\MyJobListener
        tags:
            - { name: abc.job.event_listener, event: abc.job.terminated, method: onTerminate }
```