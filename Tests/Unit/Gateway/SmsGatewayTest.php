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

use Yan\Bundle\SmsSenderBundle\Gateway\SmsGateway;

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
class SmsGatewayTest extends \PHPUnit_Framework_TestCase
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

    public function getSmsMock()
    {
        $smsMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Composer\Sms')
            ->disableOriginalConstructor()
            ->getMock();

        return $smsMock;
    }

    public function getSmsComposerMock()
    {
        $smsComposerMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Composer\SmsComposer')
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
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsGateway::getUrl
     */
    public function testGetUrl()
    {
        $value = null;
        $stub = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Gateway\SmsGateway')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $stub->expects($this->any())
             ->method('getUrl')
             ->will($this->returnValue($value));

        $this->assertEquals($value, $stub->getUrl());
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsGateway::getName
     */
    public function testGetName()
    {
        $value = null;
        $stub = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Gateway\SmsGateway')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $stub->expects($this->any())
             ->method('getName')
             ->will($this->returnValue($value));

        $this->assertEquals($value, $stub->getName());
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsGateway::getGatewayConfiguration
     */
    public function testGetGatewayConfiguration()
    {
        $curlMock = $this->getCurlMock();
        $configurationMock = $this->getConfigurationMock();
        $smsComposerMock = $this->getSmsComposerMock();
        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();

        $configurationMock->expects($this->any())
            ->method('getGatewayConfigurationByApiName')
            ->will($this->returnValue(null));

        $value = null;
        $stub = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Gateway\SmsGateway')
            ->setConstructorArgs(array($configurationMock, $curlMock, $smsComposerMock))
            ->getMockForAbstractClass();

        $stub->expects($this->any())
             ->method('getGatewayConfiguration')
             ->will($this->returnValue($gatewayConfigurationMock));
        
        $this->assertEquals($value, $stub->getGatewayConfiguration());
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsGateway::send
     */
    public function testSendWithDeliveryDisabled()
    {
        $curlMock = $this->getCurlMock();
        $configurationMock = $this->getConfigurationMock();
        $smsComposerMock = $this->getSmsComposerMock();
        $smsMock = $this->getSmsMock();

        $configurationMock->expects($this->any())
            ->method('isDeliveryEnabled')
            ->will($this->returnValue(false));

        $value = null;
        $stub = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Gateway\SmsGateway')
            ->setConstructorArgs(array($configurationMock, $curlMock, $smsComposerMock))
            ->getMockForAbstractClass();

        $this->assertEquals($value, $stub->send($smsMock));
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsGateway::send
     */
    public function testSendSuccess()
    {
        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $smsMock = $this->getSmsMock();

        $curlMock = $this->getCurlMock();
        $curlMock->expects($this->any())
            ->method('post')
            ->will($this->returnValue(true));

        $configurationMock = $this->getConfigurationMock();
        $configurationMock->expects($this->any())
            ->method('isDeliveryEnabled')
            ->will($this->returnValue(true));

        $configurationMock->expects($this->any())
            ->method('getGatewayConfigurationByApiName')
            ->will($this->returnValue($gatewayConfigurationMock));
        
        $smsComposerMock = $this->getSmsComposerMock();
        $smsComposerMock->expects($this->any())
            ->method('compose')
            ->with($smsMock, $gatewayConfigurationMock)
            ->will($this->returnValue(array($smsMock)));
        
        $stub = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Gateway\SmsGateway')
            ->setConstructorArgs(array($configurationMock, $curlMock, $smsComposerMock))
            ->getMockForAbstractClass();

        $this->assertTrue($stub->send($smsMock));
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsGateway::send
     */
    public function testSendThrowsException()
    {
        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $smsMock = $this->getSmsMock();

        $curlMock = $this->getCurlMock();
        $curlMock->expects($this->any())
            ->method('post')
            ->will($this->returnValue(true));

        $configurationMock = $this->getConfigurationMock();
        $configurationMock->expects($this->any())
            ->method('isDeliveryEnabled')
            ->will($this->returnValue(true));

        $configurationMock->expects($this->any())
            ->method('getGatewayConfigurationByApiName')
            ->will($this->returnValue($gatewayConfigurationMock));
        
        $smsComposerMock = $this->getSmsComposerMock();
        $smsComposerMock->expects($this->any())
            ->method('compose')
            ->with($smsMock, $gatewayConfigurationMock)
            ->will($this->returnValue(array($smsMock)));
        
        $stub = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Gateway\SmsGateway')
            ->setConstructorArgs(array($configurationMock, $curlMock, $smsComposerMock))
            ->getMockForAbstractClass();

        $stub->expects($this->any())
            ->method('handleResult')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException('Request sending failed.')));

        $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException');
        $this->assertTrue($stub->send($smsMock));
    }
}
