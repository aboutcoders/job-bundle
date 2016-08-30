<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class TypeControllerTest extends WebTestCase
{
    public function testCgetAction()
    {
        $client = static::createClient();

        $url = '/api/types';

        $client->request(
            'GET',
            $url,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            null,
            'json'
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        $this->assertContains('abc.mailer', $data);
    }
}
