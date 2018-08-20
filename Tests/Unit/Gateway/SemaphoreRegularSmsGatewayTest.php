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

use Yan\Bundle\SmsSenderBundle\Gateway\SemaphoreRegularSmsGateway;

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
class SemaphoreRegularSmsGatewayTest extends \PHPUnit_Framework_TestCase
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

    public function getGatewayConfigurationMock()
    {
        $gatewayConfigurationMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Tools\GatewayConfiguration')
            ->disableOriginalConstructor()
            ->getMock();

        return $gatewayConfigurationMock;
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SemaphoreRegularSmsGateway::getUrl
     */
    public function testGetUrl()
    {
        $curlMock = $this->getCurlMock();
        $configurationMock = $this->getConfigurationMock();
        $smsComposerMock = $this->getSmsComposerMock();

        $expected = 'http://api.semaphore.co/api/v4/messages';
        $sut = new SemaphoreRegularSmsGateway($configurationMock, $curlMock, $smsComposerMock);

        $this->assertEquals($expected, $sut->getUrl());
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SemaphoreRegularSmsGateway::getName
     */
    public function testGetName()
    {
        $curlMock = $this->getCurlMock();
        $configurationMock = $this->getConfigurationMock();
        $smsComposerMock = $this->getSmsComposerMock();

        $expected = 'SEMAPHORE_REGULAR';
        $sut = new SemaphoreRegularSmsGateway($configurationMock, $curlMock, $smsComposerMock);

        $this->assertEquals($expected, $sut->getName());
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SemaphoreRegularSmsGateway::getGatewayConfiguration
     */
    public function testGetGatewayConfiguration()
    {
        $curlMock = $this->getCurlMock();
        $configurationMock = $this->getConfigurationMock();
        $smsComposerMock = $this->getSmsComposerMock();
        
        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $gatewayConfigurationMock->expects($this->any())
             ->method('getApiName')
             ->will($this->returnValue('SEMAPHORE_REGULAR'));

        $configurationMock->expects($this->any())
            ->method('getGatewayConfigurationByApiName')
            ->will($this->returnValue($gatewayConfigurationMock));

        $expected = 'SEMAPHORE_REGULAR';
        $sut = new SemaphoreRegularSmsGateway($configurationMock, $curlMock, $smsComposerMock);
        
        $this->assertEquals($expected, $sut->getGatewayConfiguration()->getApiName());
    }
}
