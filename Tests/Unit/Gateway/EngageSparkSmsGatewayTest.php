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

use Yan\Bundle\SmsSenderBundle\Gateway\EngageSparkSmsGateway;

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
class EngageSparkSmsGatewayTest extends \PHPUnit_Framework_TestCase
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
        $smsComposerMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Composer\EngageSparkSmsComposer')
            ->disableOriginalConstructor()
            ->getMock();

        return $smsComposerMock;
    }

    public function getGatewayConfigurationMock()
    {
        $gatewayConfigurationMock = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Tools\GatewayConfiguration')
            ->disableOriginalConstructor()
            ->getMock();

        return $gatewayConfigurationMock;
    }

    public function getTestHandleResult()
    {
        return array(
            array(array(array('status' => 'Success')), true),
            array("", false),
            array(null, false),
            array(array('detail' => 'Authentication credentials were not provided.'), true),
            array(array('error' => 'Any error'), true),
            array(true, true),
            array('test', true)
        );
    }

    public function getTestGetAccountBalance()
    {
        return array(
            array('{"balance": "99.92"}', 6661, false),
            array('{"balance": "2000"}', 133333, false),
            array('{"balances": "99.92"}', 2000, true)
        );
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/EngageSparkSmsGateway::getUrl
     */
    public function testGetUrl()
    {
        $curlMock = $this->getCurlMock();
        $configurationMock = $this->getConfigurationMock();
        $smsComposerMock = $this->getSmsComposerMock();

        $expected = 'https://start.engagespark.com/api/v1/messages/sms';
        $sut = new EngageSparkSmsGateway($configurationMock, $curlMock, $smsComposerMock);

        $this->assertEquals($expected, $sut->getUrl());
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/EngageSparkSmsGateway::getName
     */
    public function testGetName()
    {
        $curlMock = $this->getCurlMock();
        $configurationMock = $this->getConfigurationMock();
        $smsComposerMock = $this->getSmsComposerMock();

        $expected = 'ENGAGE_SPARK';
        $sut = new EngageSparkSmsGateway($configurationMock, $curlMock, $smsComposerMock);

        $this->assertEquals($expected, $sut->getName());
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/EngageSparkSmsGateway::getGatewayConfiguration
     */
    public function testGetGatewayConfiguration()
    {
        $curlMock = $this->getCurlMock();
        $configurationMock = $this->getConfigurationMock();
        $smsComposerMock = $this->getSmsComposerMock();
        
        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $gatewayConfigurationMock->expects($this->any())
             ->method('getApiName')
             ->will($this->returnValue('ENGAGE_SPARK'));

        $configurationMock->expects($this->any())
            ->method('getGatewayConfigurationByApiName')
            ->will($this->returnValue($gatewayConfigurationMock));

        $expected = 'ENGAGE_SPARK';
        $sut = new EngageSparkSmsGateway($configurationMock, $curlMock, $smsComposerMock);
        
        $this->assertEquals($expected, $sut->getGatewayConfiguration()->getApiName());
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/EngageSparkSmsGateway::send
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

        $expected = null;
        $sut = new EngageSparkSmsGateway($configurationMock, $curlMock, $smsComposerMock);

        $this->assertEquals($expected, $sut->send($smsMock));
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/EngageSparkSmsGateway::send
     */
    public function testSendSuccess()
    {
        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $smsMock = $this->getSmsMock();

        $curlMock = $this->getCurlMock();
        $curlMock->expects($this->any())
            ->method('post')
            ->will($this->returnValue('[]'));

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
        
        $sut = new EngageSparkSmsGateway($configurationMock, $curlMock, $smsComposerMock);

        $this->assertTrue($sut->send($smsMock));
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/EngageSparkSmsGateway::send
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
        
        $stub = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Gateway\EngageSparkSmsGateway')
            ->setConstructorArgs(array($configurationMock, $curlMock, $smsComposerMock))
            ->getMockForAbstractClass();

        $stub->expects($this->any())
            ->method('handleResult')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException('Request sending failed.')));

        $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException');
        $stub->send($smsMock);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/EngageSparkSmsGateway::handleResult
     * @dataProvider getTestHandleResult
     */
    public function testHandleResult($result, $throwException)
    {
        $curlMock = $this->getCurlMock();
        $configurationMock = $this->getConfigurationMock();
        $smsComposerMock = $this->getSmsComposerMock();

        $sut = new EngageSparkSmsGateway($configurationMock, $curlMock, $smsComposerMock);
        
        if ($throwException) {
            $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException');
        }

        $this->assertEquals(null, $sut->handleResult(json_encode($result)));
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/EngageSparkSmsGateway::getAccountBalance
     * @dataProvider getTestGetAccountBalance
     */
    public function testGetAccountBalance($result, $expected, $throwException)
    {
        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $gatewayConfigurationMock->expects($this->any())
             ->method('getApiKey')
             ->will($this->returnValue('API_KEY'));

        $gatewayConfigurationMock->expects($this->any())
             ->method('getPricePerSms')
             ->will($this->returnValue(0.015));

        $curlMock = $this->getCurlMock();
        $curlMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($result));

        $configurationMock = $this->getConfigurationMock();
        $configurationMock->expects($this->any())
            ->method('getGatewayConfigurationByApiName')
            ->will($this->returnValue($gatewayConfigurationMock));

        $smsComposerMock = $this->getSmsComposerMock();

        // $stub = new SemaphoreSmsGateway($configurationMock, $curlMock, $smsComposerMock);
        $stub = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Gateway\EngageSparkSmsGateway')
            ->setConstructorArgs(array($configurationMock, $curlMock, $smsComposerMock))
            ->getMockForAbstractClass();

        $stub->expects($this->any())
            ->method('getGatewayConfiguration')
            ->will($this->returnValue($gatewayConfigurationMock));

        if ($throwException) {
            $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException');
        }

        $this->assertEquals($expected, $stub->getAccountBalance());
    }
}
