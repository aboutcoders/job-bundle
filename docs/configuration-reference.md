Configuration Reference
=======================

``yaml
# app/config.yml
abc_job:
    db_driver: orm                # allowed values: [orm]
    register_default_jobs: true   # allowed values: [true,false] default is true
    logging:
        handler: file             # allowed values: [file|orm], default value is file
        directory: 'path/to/dir'  # only applied if handler is file
        default_level: debug      # allowed values [debug|info|notice|warning|error|critical|alert]
        custom_level:             # specific log level for jobs (see: Logging)
        formatter:                # logging formatters (see: Logging)
        processor:                # logging formatters (see: Logging)
```