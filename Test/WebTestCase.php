<?php
/*
* This file is part of the wcm-backend package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Test;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * Replaces services with mock instances
     *
     * @param array $services An associative array with the service id as key and the service instance as value
     * @see http://blog.lyrixx.info/2013/04/12/symfony2-how-to-mock-services-during-functional-tests.html
     */
    protected function mockServices(array $services)
    {
        /**
         * @ignore
         */
        static::$kernel->setKernelModifier(
            function (KernelInterface $kernel) use ($services) {
                foreach ($services as $id => $service) {
                    $kernel->getContainer()->set($id, $service);
                }
            }
        );
    }
}