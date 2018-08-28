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

    public function getSmsMock()
    {
        $smsMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Composer\Sms')
            ->disableOriginalConstructor()
            ->getMock();

        return $smsMock;
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
        $gatewayConfigurationMock = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Tools\GatewayConfiguration')
            ->disableOriginalConstructor()
            ->getMock();

        return $gatewayConfigurationMock;
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

    public function getTestGetAccountBalance()
    {
        return array(
            array(array('credit_balance' => 2000), 2000, false),
            array(array(array('credit_balance' => 2000)), 2000, true),
            array(array('credit_balances' => 2000), 2000, true)
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

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SemaphoreSmsGateway::getAccountBalance
     * @dataProvider getTestGetAccountBalance
     */
    public function testGetAccountBalance($result, $expected, $throwException)
    {
        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $gatewayConfigurationMock->expects($this->any())
             ->method('getApiKey')
             ->will($this->returnValue('API_KEY'));

         $curlMock = $this->getCurlMock();
        $curlMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(json_encode($result)));

        $configurationMock = $this->getConfigurationMock();
        $configurationMock->expects($this->any())
            ->method('getGatewayConfigurationByApiName')
            ->will($this->returnValue($gatewayConfigurationMock));

        $smsComposerMock = $this->getSmsComposerMock();

        // $stub = new SemaphoreSmsGateway($configurationMock, $curlMock, $smsComposerMock);
        $stub = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Gateway\SemaphoreSmsGateway')
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

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SemaphoreSmsGateway::send
     */
    public function testSendSuccess()
    {
        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $smsMock = $this->getSmsMock();

        $curlMock = $this->getCurlMock();
        $curlMock->expects($this->any())
            ->method('post')
            ->will($this->returnValue('[{"status":"Success"}]'));

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
        
        $sut = new SemaphoreSmsGateway($configurationMock, $curlMock, $smsComposerMock);

        $this->assertTrue($sut->send($smsMock));
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SemaphoreSmsGateway::send
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
}
