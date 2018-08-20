<?php

/*
 * This file is part of SmsSenderBundle.
 *
 * Yan Barreta <augustianne.barreta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yan\Bundle\SmsSenderBundle\Tests\Unit\Gateway;

use Yan\Bundle\SmsSenderBundle\Gateway\SemaphoreSmsGateway;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;

/**
 * Unit test for SingleSmsSender
 *
 * @author  Yan Barreta
 * @version dated: August 13, 2018
 */
class SemaphoreSmsGatewayTest extends \PHPUnit_Framework_TestCase
{
    private $sut;
    private $root;
    
    public function getConfigurationMock()
    {
        $configurationMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Tools\Accessor\ConfigurationAccessor')
            ->disableOriginalConstructor()
            ->getMock();

        return $configurationMock;
    }

    public function getCurlMock()
    {
        $curlMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Tools\Request\Curl')
            ->disableOriginalConstructor()
            ->getMock();

        return $curlMock;
    }

    public function getSmsComposerMock()
    {
        $smsComposerMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Composer\SemaphoreSmsComposer')
            ->disableOriginalConstructor()
            ->getMock();

        return $smsComposerMock;
    }

    public function getTestHandleResult()
    {
        return array(
            array(array(array('status' => 'Success')), false),
            array(array(array('test' => 'Success')), true),
            array(array('test' => 'Success'), true),
            array(true, true),
            array('test', true),
            array(array('status' => 'Failed'), true),
            array(array(array('status' => 'Failed')), true),
        );
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SemaphoreSmsGateway::handleResult
     * @dataProvider getTestHandleResult
     */
    public function testHandleResult($result, $throwException)
    {
        $curlMock = $this->getCurlMock();
        $configurationMock = $this->getConfigurationMock();
        $smsComposerMock = $this->getSmsComposerMock();

        $sut = new SemaphoreSmsGateway($configurationMock, $curlMock, $smsComposerMock);
        
        if ($throwException) {
            $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException');
        }

        $this->assertEquals(null, $sut->handleResult(json_encode($result)));
    }

    
}
