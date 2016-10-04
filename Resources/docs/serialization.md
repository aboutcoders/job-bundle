Serialization
=============

Serialization of parameters and return values of a job happens in two places. First place is when a job is persisted to the database. When this happens the parameters and return value of a job is serialized and persisted to the database together with the rest of the data of a job. The second place is the REST-Api. In this case the whole job instance including parameters and response are serialized.

The AbcJobBundle uses the [JMS serializer](https://github.com/schmittjoh/serializer) by default for serialization.

## Serialization Options

You can configure serialization options for the parameters and return value of a job.

```php
namespace My\Bundle\ExampleBundle\Job\MyJob;

use Abc\Bundle\JobBundle\Annotation\JobParameters;
use My\Bundle\ExampleBundle\Entity\MyEntity;

class MyJob
{
    private $entityManager;
    
    /**
     * @ParamType("entity", type="My\Bundle\ExampleBundle\Entity\MyEntity", options={"groups"={"primarykey"}, "version"="1"})
     * @ReturnType("My\Bundle\ExampleBundle\Model\SomeObject", options={"groups"={"mygroup"}, "version"="2")
     */
    public function doSomething($entity)
    {
        if(!$this->entityManager->contains($entity)
        {
            $entity = $this->entityManager->findByPK($entity::class, $entity->getId())
        }
        
        // ...
        
        return $someObject;
    }
}
```
__Note:__ The serialization groups are only applied when a job is persisted and loaded from the database. The reason for this is that the [JMS serializer](https://github.com/schmittjoh/serializer) so far does not support the definition of serialization groups for specific properties of an entity but only globally for the whole serialization of an object.