Configuration Reference
=======================

```yaml
# app/config/config.yml
abc_job:
    adapter: ~                      # Set it to "sonata", "bernard" or "custom"
    db_driver: orm                  # Set it to "orm" or "custom"
    register_default_jobs: false    # Whether to register the default jobs 
    model_manager_name: null        # The name of the entity manager if you do not want to use the default one
    connection: default             # The name of the doctrine connection
    default_queue_name: default     # Set name of the doctrine entity manager
    queues:                         # Define multiple queues and assign jobs to each queue
        queue_A:
            - job_type_A1
            - job_type_A2
        queue_B:
            - job_type_B1
            - job_type_B2
    controller:
        refresh_interval: 1         # The interval in seconds the database is checked if job has been cancelled
    manager:
        validate: true              # Set to false to disable validation
    rest:
        enable: true                # Set to false to disable loading of REST API related services
        validate: false             # Set to true to enable validation of jobs (ignored if validation for manager is enabled)
    logging:                        
        storage_handler:
            type: file         # Choose "orm" if you want to store job logs in the database instead of files
            path: %kernel.logs_dir% # The directory where the json encoded logs are stored (ignored if handler is "orm")
            level: info             # The minimum logging level
            bubble: false           # Whether the messages that are handled can bubble up the stack or not
            processor:
                - my_processor_id   # An array of service ids of additional processors to register
        stream_handler:             # If defined a standard stream handler will be registered
            path: %kernel.logs_dir% # The directory where log files are stored
            level: info            # The minimum logging level
            bubble: false           # Whether the messages that are handled can bubble up the stack or not
            formatter: ~            # Set a different formatter than LineFormattter
            processor:
                - my_processor_id   # An array of service ids of additional processors to register
        handler:
            - my_handler_id         # An array of service ids of additional handlers to register
        level:               
            my_job_type: debug      # sets log level for a specific job type
        
    service:
        manager:                    abc.job.manager.default
        job_manager:                abc.job.job_manager.default
        schedule_manager:           abc.job.schedule_manager.default
        schedule_iterator:          abc.job.schedule_iterator.default
        schedule_manager_iterator:  abc.job.schedule_manager_iterator.default
        controller:                 abc.process_control.controller
        controller_factory:         abc.job.controller_factory.default
        queue_config:               abc.job.queue_config.default
        serializer:                 abc.job.serializer.default
        validator:                  validator
```