<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Request;

use Abc\Bundle\JobBundle\Job\JobType;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Request\RequestBodyParameterConverter;
use Metadata\MetadataFactoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class RequestBodyParameterConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParamConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paramConverter;

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var JobTypeRegistry
     */
    protected $registry;

    /**
     * @var ParamConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $converter;

    /**
     * @var RequestBodyParameterConverter
     */
    protected $subject;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dispatcher;

    public function setUp()
    {
        $this->paramConverter = $this->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter')->disableOriginalConstructor()->getMock();
        $this->metadataFactory = $this->getMock('Metadata\MetadataFactoryInterface');
        $this->registry   = new JobTypeRegistry($this->metadataFactory);
        $this->converter  = $this->getMock('Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface');
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->subject    = new RequestBodyParameterConverter($this->registry, $this->converter, $this->dispatcher);
    }

    public function testApplyWithRequestBody()
    {
        $jobType = new JobType('service-id', 'foobar', function(){});
        $jobType->setParameterTypes(array('type1', 'type2'));
        $this->registry->register($jobType);

        $request = new Request(array(), array(), array(), array(), array(), array(), 'requestBody');
        $request->attributes->set('type', 'foobar');

        $this->paramConverter->expects($this->once())
            ->method('setClass')
            ->with('GenericArray<type1,type2>');

        $this->converter->expects($this->once())
            ->method('apply')
            ->with($request, $this->paramConverter)
            ->willReturn('result');

        $this->assertEquals('result', $this->subject->apply($request, $this->paramConverter));
    }

    public function testApplyWithEmptyRequestBody()
    {
        $request = new Request();
        $request->attributes->set('type', 'foobar');

        $this->registry->register(new JobType('service-id', 'foobar', function(){}));

        $this->assertTrue($this->subject->apply($request, $this->paramConverter));
    }

    /**
     * @param $parameterTypes
     * @dataProvider getEmptyParameterTypes
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testApplyWithRequestBodyAndWithoutParameterTypes($parameterTypes = null)
    {
        $jobType = new JobType('service-id', 'foobar', function(){});
        $jobType->setParameterTypes($parameterTypes);
        $this->registry->register($jobType);

        $request = new Request(array(), array(), array(), array(), array(), array(), 'requestBody');
        $request->attributes->set('type', 'foobar');

        $this->subject->apply($request, $this->paramConverter);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testApplyWithNoType()
    {
        $request        = new Request();
        $paramConverter = $this->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter')->disableOriginalConstructor()->getMock();

        $this->subject->apply($request, $paramConverter);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testApplyWithJobTypeNotFound()
    {
        $request = new Request();
        $request->attributes->set('type', 'foobar');

        $this->subject->apply($request, $this->paramConverter);
    }

    public function testSupports()
    {
        $paramConverter = $this->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter')->disableOriginalConstructor()->getMock();

        $this->assertTrue($this->subject->supports($paramConverter));
    }

    /**
     * @return array
     */
    public static function getEmptyParameterTypes()
    {
        return array(
            array(),
            array(array())
        );
    }
}