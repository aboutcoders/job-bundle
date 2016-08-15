Configuration Reference
=======================

```yaml
# app/config.yml
abc_job:
    db_driver: orm                # Required, allowed values: [orm]
    register_default_jobs: true   # Optional, whether the default jobs "mailer" and "sleeper" should be registered
    model_manager_name:           # Optional, the name of the entity manager that is used
    logging:                      # Optional
        handler: file             # Optional, whether logs entries are saved in a file or the database [file|orm], file by default
        directory: 'path/to/dir'  # Optional, the directory where job logs are saved (only applied if handler is file)
        default_level: debug      # Optional, the log level, allowed values [debug|info|notice|warning|error|critical|alert]
        custom_level:             # specific log level for jobs (see: Logging)
        formatter:                # logging formatters (see: Logging)
        processor:                # logging formatters (see: Logging)
```