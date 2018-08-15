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
use Yan\Bundle\SmsSenderBundle\Composer\EngageSparkSmsComposer;

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
class EngageSparkSmsComposerTest extends \PHPUnit_Framework_TestCase
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

    public function getStubForTest()
    {
        $stub = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Composer\EngageSparkSmsComposer')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return $stub;
    }

    public function getTestInternationalizeNumbersCorrectType()
    {
        return array(
            array(array('63917314906000'), array('63917314906000')),
            array(array('63.917.314.9060'), array('639173149060')),
            array(array('0917-314-9060'), array('639173149060')),
            array(array('+659995314906'), array('659995314906')),
            array(array('+6309173149060'), array()),
            array(array('09173149060'), array('639173149060')),
            array(array('+639995314906'), array('639995314906')),
            array(array('+639995314906', '+6309995314906'), array('639995314906')),
        );
    }

    public function getTestInternationalizeNumbersWrongType()
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
            array(array('63.917.314.9060'), '["639173149060"]'),
            array(array('0917-314-9060'), '["639173149060"]'),
            array(array('+659995314906'), '["659995314906"]'),
            array(array('+6309173149060'), '[]'),
            array(array('09173149060'), '["639173149060"]'),
            array(array('+639995314906'), '["639995314906"]'),
            array(array('+639995314906', '+6309995314906'), '["639995314906"]'),
            array(array('+639995314906', '09995314907'), '["639995314906","639995314907"]'),
        );
    }

    /**
     * @covers Yan/Bundle/SenderSmsBundle/Composer/SmsComposer::splitSms
     * @dataProvider getTestInternationalizeNumbersCorrectType
     */
    public function testInternationalizeNumbersCorrectRecipientType($value, $expected)
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

        $sut = new EngageSparkSmsComposer($configurationMock);
        $sut->internationalizeNumbers($sms, $gatewayConfigurationMock);
        
        // var_dump($recipientsBeforeCleaning, $sms->getRecipients());

        $this->assertEquals($expected, $sms->getRecipients());
    }

    /**
     * @covers Yan/Bundle/SenderSmsBundle/Composer/SmsComposer::splitSms
     * @dataProvider getTestInternationalizeNumbersWrongType
     */
    public function testInternationalizeNumbersWrongRecipientType($value, $expected)
    {
        $sms = new Sms();
        $sms->setFrom('AUTODEAL');
        $sms->setRecipients($value);

        $configurationMock = $this->getConfigurationMock();

        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $gatewayConfigurationMock->expects($this->any())
            ->method('getRecipientType')
            ->will($this->returnValue('contact_id'));

        $gatewayConfigurationMock->expects($this->any())
            ->method('getDefaultCountryCode')
            ->will($this->returnValue('63'));

        $sut = new EngageSparkSmsComposer($configurationMock);
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
            ->will($this->returnValue('mobile_number'));

        $gatewayConfigurationMock->expects($this->any())
            ->method('getDefaultCountryCode')
            ->will($this->returnValue('63'));

        $sut = new EngageSparkSmsComposer($configurationMock);
        $actual = $sut->formatRecipientsForSending($sms, $gatewayConfigurationMock);
        
        // var_dump($recipientsBeforeCleaning, $sms->getRecipients());

        $this->assertEquals($expected, $actual);
    }
}