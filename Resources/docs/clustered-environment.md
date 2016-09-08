Clustered Environment
=====================

If you want to use AbcJobBundle for a setup where you have multiple job processing nodes you have to make sure, that the same job cannot be processed concurrently by different nodes. This can happen if a longer running job is configured with schedules. To ensure that this cannot happen the AbcJobBundle integrates concept of a [resource lock](https://github.com/aboutcoders/resource-lock-bundle). Whenever a message is consumed from the queue the [job manager](./job-management.md) checks if the job is currently processed by another node and skips execution if this is the case.

This feature is disabled by default. In order to enable it you have to install the [AbcResourceLockBundle](https://github.com/aboutcoders/resource-lock-bundle).