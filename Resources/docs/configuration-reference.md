Configuration Reference
=======================

```yaml
# app/config/config.yml
abc_job:
    adapter: ~                      # Set it to "sonata", "bernard" or "custom"
    db_driver: orm                  # Set it to "orm" or "custom"
    register_default_jobs: false    # Whether to register the default jobs 
    model_manager_name: null        # The name of the entity manager if you do not want to use the default one
    default_queue_name: default     # Set the default queue name
    queues:                         # Define multiple queues and assign jobs to each queue
        queue_A:
            - job_type_A1
            - job_type_A2
        queue_B:
            - job_type_B1
            - job_type_B2
    controller:
        refresh_interval: 1         # The interval in seconds the database is checked if job has been cancelled
    rest:
        enable: true                # Set to false to disable loading of REST API related services
        validate: false             # Set to true to enable validation of jobs (ignored if validation for manager is enabled)
    logging:                        
        handler: file               # Choose "orm" if you want to store job logs in the database instead of files
        directory: 'path/to/dir'    # The directory where job logs are saved (ignored if handler is "orm")
        default_level: info         # The default log level of all jobs, choose one of [debug|info|notice|warning|error|critical|alert]
        custom_level:               
            my_job_type: debug      # sets log level for a specific job type
        processor:
            - my_service_id         # An array of service ids of processors
    service:
        manager:                    abc.job.manager.default
        job_manager:                abc.job.job_manager.default
        agent_manager:              abc.job.agent_manager.default
        schedule_manager:           abc.job.schedule_manager.default
        schedule_iterator:          abc.job.schedule_iterator.default
        schedule_manager_iterator:  abc.job.schedule_manager_iterator.default
        controller_factory:         abc.job.controller_factory.default
        queue_config:               abc.job.queue_config.default
        validator:                  validator
```