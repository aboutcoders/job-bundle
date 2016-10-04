<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Doctrine;

use Abc\Bundle\JobBundle\Model\Job as BaseJob;
use Abc\Bundle\JobBundle\Serializer\Job\SerializationHelper;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 *
 * @JMS\ExclusionPolicy("all")
 */
class Job extends BaseJob
{
    /**
     * @var SerializationHelper
     */
    protected static $serializationHelper;

    /**
     * @var string|null
     */
    protected $serializedParameters;

    /**
     * @var string|null
     */
    protected $serializedResponse;

    /**
     * @var bool
     */
    private $paramDeserializationError = false;

    /**
     * @var bool
     */
    private $responseDeserializationError = false;

    /**
     * @param SerializationHelper $serializationHelper
     * @return void
     */
    public static function setSerializationHelper(SerializationHelper $serializationHelper)
    {
        static::$serializationHelper = $serializationHelper;
    }

    /**
     * @param array|null $parameters
     * @throws \InvalidArgumentException If serialization of parameters fails
     * @throws \RuntimeException
     * @return void
     */
    public function setParameters($parameters = null)
    {
        parent::setParameters($parameters);

        try {
            $this->serializedParameters = ($parameters == null ? null : static::getSerializationHelper()->serializeParameters($this->getType(), $parameters));

        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Failed to serialize parameters', null, $e);
        }
    }

    /**
     * @return array|null
     * @throws \Exception If deserialization fails
     */
    public function getParameters()
    {
        if ($this->paramDeserializationError) {
            return null;
        }

        if (is_null(parent::getParameters()) && !is_null($this->serializedParameters)) {
            try {
                parent::setParameters(static::getSerializationHelper()->deserializeParameters($this->getType(), $this->serializedParameters));
            } catch (\Exception $e) {

                $this->paramDeserializationError = true;
                throw $e;
            }
        }

        return parent::getParameters();
    }

    /**
     * @param mixed|null $response
     * @throws \InvalidArgumentException If serialization of parameters fails
     * @throws \RuntimeException
     * @return void
     */
    public function setResponse($response = null)
    {
        parent::setResponse($response);

        try {
            $this->serializedResponse = ($response == null ? null : static::getSerializationHelper()->serializeReturnValue($this->getType(), $response));
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Failed to serialize response', null, $e);
        }
    }

    /**
     * @return mixed
     * @throws \Exception If deserialization fails
     */
    public function getResponse()
    {
        if ($this->responseDeserializationError) {
            return null;
        }

        if (is_null(parent::getResponse()) && !is_null($this->serializedResponse)) {
            try {
                parent::setResponse(static::getSerializationHelper()->deserializeReturnValue($this->getType(), $this->serializedResponse));
            } catch (\Exception $e) {
                $this->responseDeserializationError = true;

                throw $e;
            }
        }

        return parent::getResponse();
    }

    /**
     * @return SerializationHelper
     * @throws \RuntimeException If the serializer is not set
     */
    protected static function getSerializationHelper()
    {
        if (is_null(static::$serializationHelper)) {
            throw new \RuntimeException('The serialization helper is null');
        }

        return static::$serializationHelper;
    }
}