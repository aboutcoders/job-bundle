How-to work with the job status
===============================

The status of a job is defined as an enumeration (based on [myclabs/php-enum](https://github.com/myclabs/php-enum)) with the following values:

```php
const REQUESTED  = 'REQUESTED';
const PROCESSING = 'PROCESSING';
const PROCESSED  = 'PROCESSED';
const CANCELLING = 'CANCELLING';
const CANCELLED  = 'CANCELLED';
const ERROR      = 'ERROR';
const SLEEPING   = 'SLEEPING';
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

Alternatively you can use the method ´equals´ of the Status class.

```php
$status = $manager->getStatus($ticket));

if(Status::equals(Status::PROCESSED(), $status))
{
    // ...
}
```