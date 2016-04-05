How-to work with the job status
===============================

The status of a job is defined as an enumeration (based on [myclabs/php-enum](https://github.com/myclabs/php-enum)) with the following values:

```php
const REQUESTED  = 1;
const PROCESSING = 2;
const SLEEPING   = 3;
const PROCESSED  = 4;
const CANCELLED  = 5;
const ERROR      = 6;
```

## Getting the status:

```php
$status = $manager->get($ticket)->getStatus();
```

## Status checks:

Status checks can be done like this:

```php
$status = $manager->getStatus($ticket));

if($status == Status::PROCESSED())
{
    // ...
}
```