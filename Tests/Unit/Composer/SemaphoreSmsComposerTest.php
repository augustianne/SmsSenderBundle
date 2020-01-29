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
use Yan\Bundle\SmsSenderBundle\Composer\SemaphoreSmsComposer;

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
class SemaphoreSmsComposerTest extends \PHPUnit_Framework_TestCase
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
        $stub = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Composer\SemaphoreSmsComposer')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return $stub;
    }

    public function getTestInternationalizeNumbers()
    {
        return array(
            array(array('63917314906000'), array('63917314906000')),
            array(array('63.917.314.9060'), array('63.917.314.9060')),
            array(array('0917-314-9060'), array('0917-314-9060')),
            array(array('+659995314906'), array('+659995314906')),
            array(array('+6309173149060'), array('+6309173149060')),
            array(array('09173149060'), array('09173149060')),
            array(array('+639995314906'), array('+639995314906')),
            array(array('+639995314906', '+6309995314906'), array('+639995314906', '+6309995314906')),
        );
    }

    public function getTestFormatRecipientsForSending()
    {
        return array(
            array(array('63.917.314.9060'), '63.917.314.9060'),
            array(array('0917-314-9060'), '0917-314-9060'),
            array(array('+659995314906'), '+659995314906'),
            array(array('+6309173149060'), '+6309173149060'),
            array(array('09173149060'), '09173149060'),
            array(array('+639995314906'), '+639995314906'),
            array(array('+639995314906', '+6309995314906'), '+639995314906,+6309995314906'),
            array(array('+639995314906', '09995314907'), '+639995314906,09995314907'),
        );
    }

    public function getTestComposeParameters()
    {
        return array(
            array(
                array(
                    'apikey' => 'SEMAPHORE_API_KEY',
                    'recipients' => array('09173149060'),
                    'message' => 'Formatted message',
                    'sender_id' => 'SEMAPHORE_SENDER_NAME',
                    'formatted_recipients' => '09173149060'
                ),
                array(
                    'apikey' => 'SEMAPHORE_API_KEY',
                    'number' => '09173149060',
                    'message' => 'Formatted message',
                    'sendername' => 'SEMAPHORE_SENDER_NAME',
                ), false
            ),
            array(
                array(
                    'apikey' => 'SEMAPHORE_API_KEY',
                    'recipients' => array(),
                    'message' => 'Formatted message',
                    'sender_id' => 'SEMAPHORE_SENDER_NAME',
                    'formatted_recipients' => '["639173149060"]'
                ),
                array(
                    'apikey' => 'SEMAPHORE_API_KEY',
                    'number' => '09173149060',
                    'message' => 'Formatted message',
                    'sendername' => 'SEMAPHORE_SENDER_NAME'
                ), true
            ),
            array(
                array(
                    'apikey' => 'SEMAPHORE_API_KEY',
                    'recipients' => array(),
                    'sender_id' => 'SEMAPHORE_SENDER_NAME',
                    'formatted_recipients' => '["639173149060"]'
                ),
                array(
                    'apikey' => 'SEMAPHORE_API_KEY',
                    'number' => '09173149060',
                    'message' => 'Formatted message',
                    'sendername' => 'SEMAPHORE_SENDER_NAME'
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
     * @dataProvider getTestInternationalizeNumbers
     */
    public function testInternationalizeNumbers($value, $expected)
    {
        $sms = new Sms();
        $sms->setFrom('AUTODEAL');
        $sms->setRecipients($value);

        $configurationMock = $this->getConfigurationMock();

        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $gatewayConfigurationMock->expects($this->any())
            ->method('getRecipientType')
            ->will($this->returnValue(null));

        $gatewayConfigurationMock->expects($this->any())
            ->method('getDefaultCountryCode')
            ->will($this->returnValue('63'));

        $sut = new SemaphoreSmsComposer($configurationMock);
        $sut->internationalizeNumbers($sms, $gatewayConfigurationMock);
        
        $this->assertEquals($expected, $sms->getRecipients());
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
            ->will($this->returnValue(null));

        $gatewayConfigurationMock->expects($this->any())
            ->method('getDefaultCountryCode')
            ->will($this->returnValue('63'));

        $sut = new SemaphoreSmsComposer($configurationMock);
        $actual = $sut->formatRecipientsForSending($sms, $gatewayConfigurationMock);
        
        // var_dump($recipientsBeforeCleaning, $sms->getRecipients());

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SemaphoreSmsComposer::composeSmsParameters
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

        if (isset($values['sender_id'])) {
            $gatewayConfigurationMock->expects($this->any())
             ->method('getSenderName')
             ->will($this->returnValue($values['sender_id']));
        }

        $smsMock = $this->getSmsMock();

        if (isset($values['recipients'])) {
            $smsMock->expects($this->any())
                 ->method('getRecipients')
                 ->will($this->returnValue($values['recipients']));

            $smsMock->expects($this->any())
                 ->method('setRecipients')
                 ->will($this->returnValue($values['recipients']));
        }

        if (isset($values['message'])) {
            $smsMock->expects($this->any())
             ->method('getContent')
             ->will($this->returnValue($values['message']));
        }

        $sut = new SemaphoreSmsComposer($this->getConfigurationMock());

        if ($expectException) {
            $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\InvalidGatewayParameterException');
            $sut->composeSmsParameters($smsMock, $gatewayConfigurationMock);
        }
        else {
            $this->assertEquals($expected, $sut->composeSmsParameters($smsMock, $gatewayConfigurationMock));
        }
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SemaphoreSmsComposer::composeSmsParameters
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

        $sut = new SemaphoreSmsComposer($this->getConfigurationMock());

        $params = $sut->composeSmsParameters($sms, $gatewayConfigurationMock);
        if ($useDefaultSender) {
            $this->assertEquals($params['sendername'], $defaultSender);
        }
        else {
            $this->assertEquals($params['sendername'], $senderName);
        }
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SemaphoreSmsComposer::composeSmsParameters
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

        $sut = new SemaphoreSmsComposer($this->getConfigurationMock());

        $params = $sut->composeSmsParameters($sms, $gatewayConfigurationMock);
        $this->assertEquals($params['sendername'], 'DEFAULT SENDER');
    }
}
