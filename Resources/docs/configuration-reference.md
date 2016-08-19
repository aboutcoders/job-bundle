Configuration Reference
=======================

```yaml
# app/config.yml
abc_job:
    db_driver: ~                    # Required
    adapter: sonata                 # Set it to custom if you want to use another adapter
    register_default_jobs: true     # Whether to register the default jobs 
    model_manager_name: null        # The name of the entity manager if you do not want to use the default one      
    controller:
        refresh_interval: 1         # The number of seconds seconds when database is checked if job has been cancelled
    logging:                        
        handler: file               # Choose "orm" if you want to store job logs in the database instead of files
        directory: 'path/to/dir'    # The directory where job logs are saved (ignored if handler is "orm")
        default_level: info         # The default log level, choose one of [debug|info|notice|warning|error|critical|alert]
        custom_level:               
            my_job_type: debug      # sets log level for a specific job type
        formatter: custom_formatter_id # The service id of a custom log formatter
        processor:
            - my_service_id         # An array of service ids of processors
    service:
        manager:                    abc.job.manager.default
        job_manager:         abc.job.job_manager.default
        agent_manager:       abc.job.agent_manager.default
        schedule_manager:    abc.job.schedule_manager.default
        schedule_iterator:          abc.job.schedule_iterator.default
        schedule_manager_iterator:  abc.job.schedule_manager_iterator.default
        controller_factory:         abc.job.controller_factory.default
```