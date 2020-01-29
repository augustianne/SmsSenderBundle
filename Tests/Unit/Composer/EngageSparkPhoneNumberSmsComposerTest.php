<?php

/*
 * This file is part of SmsSenderBundle.
 *
 * Yan Barreta <augustianne.barreta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yan\Bundle\SmsSenderBundle\Tests\Unit\Composer;

use Yan\Bundle\SmsSenderBundle\Composer\Sms;
use Yan\Bundle\SmsSenderBundle\Composer\SmsComposer;
use Yan\Bundle\SmsSenderBundle\Composer\EngageSparkPhoneNumberSmsComposer;

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
class EngageSparkPhoneNumberSmsComposerTest extends \PHPUnit_Framework_TestCase
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

    public function getGatewayConfigurationMock()
    {
        $gatewayConfigurationMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Tools\GatewayConfiguration')
            ->disableOriginalConstructor()
            ->getMock();

        return $gatewayConfigurationMock;
    }

    public function getSmsMock()
    {
        $smsMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Composer\Sms')
            ->disableOriginalConstructor()
            ->getMock();

        return $smsMock;
    }

    public function getStubForTest()
    {
        $stub = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Composer\EngageSparkPhoneNumberSmsComposer')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return $stub;
    }

    public function getTestFormatRecipientsForSending()
    {
        return array(
            array(array('63.917.314.9060'), '639173149060'),
            array(array('0917-314-9060'), '639173149060'),
            array(array('+659995314906'), '659995314906'),
            array(array('+6309173149060'), ''),
            array(array('09173149060'), '639173149060'),
            array(array('+639995314906'), '639995314906'),
            array(array('+639995314906', '+6309995314906'), '639995314906'),
            array(array('+639995314906', '09995314907'), '639995314906,639995314907'),
        );
    }

    public function getTestComposeParameters()
    {
        return array(
            array(
                array(
                    'apikey' => 'ENGAGE_SPARK_API_KEY',
                    'orgId' => 'ENGAGE_SPARK_ORG_ID',
                    'to' => array('09173149060'),
                    'message' => 'Formatted message',
                    'from' => 'ENGAGE_SPARK_SENDER_NAME',
                    'formatted_recipients' => '09173149060'
                ),
                array(
                    'orgId' => 'ENGAGE_SPARK_ORG_ID',
                    'to' => '09173149060',
                    'message' => 'Formatted message',
                    'from' => 'ENGAGE_SPARK_SENDER_NAME'
                ), false
            ),
            array(
                array(
                    'apikey' => 'ENGAGE_SPARK_API_KEY',
                    'orgId' => 'ENGAGE_SPARK_ORG_ID',
                    'to' => array('639173149060'),
                    'message' => 'Formatted message',
                    'from' => 'ENGAGE_SPARK_SENDER_NAME',
                    'formatted_recipients' => '639173149060'
                ),
                array(
                    'orgId' => 'ENGAGE_SPARK_ORG_ID',
                    'to' => '639173149060',
                    'message' => 'Formatted message',
                    'from' => 'ENGAGE_SPARK_SENDER_NAME'
                ), false
            ),
            array(
                array(
                    'apikey' => 'ENGAGE_SPARK_API_KEY',
                    'orgId' => 'ENGAGE_SPARK_ORG_ID',
                    'to' => array('09173149060'),
                    'message' => 'Formatted message',
                    'from' => 'ENGAGE_SPARK_SENDER_NAME',
                    'formatted_recipients' => '639173149060'
                ),
                array(
                    'orgId' => 'ENGAGE_SPARK_ORG_ID',
                    'to' => '09173149060',
                    'message' => 'Formatted message',
                    'from' => 'ENGAGE_SPARK_SENDER_NAME'
                ), false
            ),
            array(
                array(
                    'apikey' => 'ENGAGE_SPARK_API_KEY',
                    'to' => array('09173149060'),
                    'message' => 'Formatted message',
                    'from' => 'ENGAGE_SPARK_SENDER_NAME',
                    'formatted_recipients' => '639173149060'
                ),
                array(
                    'orgId' => 'ENGAGE_SPARK_ORG_ID',
                    'to' => '639173149060',
                    'message' => 'Formatted message',
                    'from' => 'ENGAGE_SPARK_SENDER_NAME'
                ), true
            ),
            array(
                array(
                    'apikey' => 'ENGAGE_SPARK_API_KEY',
                    'orgId' => 'ENGAGE_SPARK_ORG_ID',
                    'to' => array(),
                    'message' => 'Formatted message',
                    'from' => 'ENGAGE_SPARK_SENDER_NAME',
                    'formatted_recipients' => '639173149060'
                ),
                array(
                    'orgId' => 'ENGAGE_SPARK_ORG_ID',
                    'to' => '639173149060',
                    'message' => 'Formatted message',
                    'from' => 'ENGAGE_SPARK_SENDER_NAME'
                ), true
            )
        );
    }

    public function getTestSenderName()
    {
        return array(
            array('THIS_SENDER', 'DEFAULT_SENDER', false),
            array(null, 'DEFAULT_SENDER', true),
            array('', 'DEFAULT_SENDER', true)
        );
    } 

    /**
     * @covers Yan/Bundle/SenderSmsBundle/Composer/SmsComposer::splitSms
     * @dataProvider getTestFormatRecipientsForSending
     */
    public function testFormatRecipientsForSending($value, $expected)
    {
        $sms = new Sms();
        $sms->setFrom('AUTODEAL');
        $sms->setRecipients($value);

        $configurationMock = $this->getConfigurationMock();
        
        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $gatewayConfigurationMock->expects($this->any())
            ->method('getRecipientType')
            ->will($this->returnValue('mobile_number'));

        $gatewayConfigurationMock->expects($this->any())
            ->method('getDefaultCountryCode')
            ->will($this->returnValue('63'));

        $sut = new EngageSparkPhoneNumberSmsComposer($configurationMock);
        $actual = $sut->formatRecipientsForSending($sms, $gatewayConfigurationMock);
        
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/EngageSparkPhoneNumberSmsComposer::composeSmsParameters
     * @dataProvider getTestComposeParameters
     */
    public function testComposeParameters($values, $expected, $expectException)
    {
        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        if (isset($values['apikey'])) {
            $gatewayConfigurationMock->expects($this->any())
                ->method('getApiKey')
                ->will($this->returnValue($values['apikey']));
        }

        if (isset($values['orgId'])) {
            $gatewayConfigurationMock->expects($this->any())
                ->method('getOrganizationId')
                ->will($this->returnValue($values['orgId']));
        }

        if (isset($values['from'])) {
            $gatewayConfigurationMock->expects($this->any())
                ->method('getSenderName')
                ->will($this->returnValue($values['from']));
        }

        $smsMock = $this->getSmsMock();
        if (isset($values['to'])) {
            $smsMock->expects($this->any())
                ->method('getRecipients')
                ->will($this->returnValue($values['to']));

            $smsMock->expects($this->any())
                ->method('setRecipients')
                ->will($this->returnValue($values['to']));
        }

        if (isset($values['message'])) {
            $smsMock->expects($this->any())
                ->method('getContent')
                ->will($this->returnValue($values['message']));
        }

        $sut = new EngageSparkPhoneNumberSmsComposer($this->getConfigurationMock());

        if ($expectException) {
            $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\InvalidGatewayParameterException');
            $sut->composeSmsParameters($smsMock, $gatewayConfigurationMock);
        }
        else {
            $this->assertEquals($expected, $sut->composeSmsParameters($smsMock, $gatewayConfigurationMock));
        }
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/EngageSparkPhoneNumberSmsComposer::composeSmsParameters
     * @dataProvider getTestSenderName
     */
    public function testSenderName($senderName, $defaultSender, $useDefaultSender)
    {
        $sms = new Sms();
        $sms->setFrom($senderName);
        $sms->setContent('Content');
        $sms->addRecipient('09173149060');

        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $gatewayConfigurationMock->expects($this->any())
            ->method('getSenderName')
            ->will($this->returnValue($defaultSender));

        $gatewayConfigurationMock->expects($this->any())
            ->method('getApiKey')
            ->will($this->returnValue('APIKEY'));

        $gatewayConfigurationMock->expects($this->any())
            ->method('getOrganizationId')
            ->will($this->returnValue('ORGANIZATION ID'));

        $sut = new EngageSparkPhoneNumberSmsComposer($this->getConfigurationMock());

        $params = $sut->composeSmsParameters($sms, $gatewayConfigurationMock);
        if ($useDefaultSender) {
            $this->assertEquals($params['from'], $defaultSender);
        }
        else {
            $this->assertEquals($params['from'], $senderName);
        }
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/EngageSparkPhoneNumberSmsComposer::composeSmsParameters
     */
    public function testNoSenderName()
    {
        $sms = new Sms();
        $sms->setContent('Content');
        $sms->addRecipient('09173149060');

        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $gatewayConfigurationMock->expects($this->any())
            ->method('getSenderName')
            ->will($this->returnValue('DEFAULT SENDER'));

        $gatewayConfigurationMock->expects($this->any())
            ->method('getApiKey')
            ->will($this->returnValue('APIKEY'));

        $gatewayConfigurationMock->expects($this->any())
            ->method('getOrganizationId')
            ->will($this->returnValue('ORGANIZATION ID'));

        $sut = new EngageSparkPhoneNumberSmsComposer($this->getConfigurationMock());

        $params = $sut->composeSmsParameters($sms, $gatewayConfigurationMock);
        $this->assertEquals($params['from'], 'DEFAULT SENDER');
    }
}
