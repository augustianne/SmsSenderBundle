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
class SmsComposerTest extends \PHPUnit_Framework_TestCase
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
        $stub = $this->getMockBuilder('\Yan\Bundle\SmsSenderBundle\Composer\SmsComposer')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return $stub;
    }

    public function getTestSplitSmsData()
    {
        return array(
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages',
                array(
                    array('Fr', 'AutoDeal:', 'Great', 'news,', 'your', 'listing', 'has', 'been', 'approved', 'and', 'is', 'now', 'live', 'on', 'AutoDeal.com.ph!', 'View', 'your', 'listing', 'at'),
                    array('http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages')
                )
            ),
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph!',
                array(
                    array('Fr', 'AutoDeal:', 'Great', 'news,', 'your', 'listing', 'has', 'been', 'approved', 'and', 'is', 'now', 'live', 'on', 'AutoDeal.com.ph!'),
                )
            ),
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages',
                array(
                    array('Fr', 'AutoDeal:', 'Great', 'news,', 'your', 'listing', 'has', 'been', 'approved', 'and', 'is', 'now', 'live', 'on', 'AutoDeal.com.ph!', 'View', 'your', 'listing', 'at'),
                    array('http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages')
                )
            ),
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages on AutoDeal.com.ph! View your listing at',
                array(
                    array('Fr', 'AutoDeal:', 'Great', 'news,', 'your', 'listing', 'has', 'been', 'approved', 'and', 'is', 'now', 'live'),
                    array('http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages'),
                    array('on', 'AutoDeal.com.ph!', 'View', 'your', 'listing', 'at')
                )
            ),
            array(
                'Fr AutoDeal: Your listing for the 2010 Toyota Innova E Diesel MT has now expired. To renew your listing go http://www.staging4.autodeal.com.ph/account/used-cars/inquiries',
                array(
                    array('Fr', 'AutoDeal:', 'Your', 'listing', 'for', 'the', '2010', 'Toyota', 'Innova', 'E', 'Diesel', 'MT', 'has', 'now', 'expired.', 'To', 'renew', 'your', 'listing', 'go',),
                    array('http://www.staging4.autodeal.com.ph/account/used-cars/inquiries')
                )
            )
        );
    }

    public function getTestConstructSmsesData()
    {
        return array(
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages',
                array(
                    '2/2 http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages',
                    '1/2 Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at'
                )
            ),
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph!',
                array('Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph!')
            ),
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages',
                array(
                    '2/2 http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages',
                    '1/2 Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at'
                )
            ),
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages on AutoDeal.com.ph! View your listing at',
                array(
                    '3/3 on AutoDeal.com.ph! View your listing at',
                    '2/3 http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages',
                    '1/3 Fr AutoDeal: Great news, your listing has been approved and is now live'
                )
            )
        );
    }

    public function getTestComposeWithTestDeliveryNumbersAndTruncateMessageTrueData()
    {
        return array(
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages',
                array(
                    '2/2 your listing at http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages',
                    '1/2 Recipients: 09173149060. Gateway: ENGAGE_SPARK. Message: Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View'
                )
            ),
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph!',
                array('Recipients: 09173149060. Gateway: ENGAGE_SPARK. Message: Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph!')
            ),
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages',
                array(
                    '3/3 http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages',
                    '2/3 your listing at',
                    '1/3 Recipients: 09173149060. Gateway: ENGAGE_SPARK. Message: Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View'
                )
            ),
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages on AutoDeal.com.ph! View your listing at',
                array(
                    '3/3 on AutoDeal.com.ph! View your listing at',
                    '2/3 http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages',
                    '1/3 Recipients: 09173149060. Gateway: ENGAGE_SPARK. Message: Fr AutoDeal: Great news, your listing has been approved and is now live'
                )
            )
        );
    }

    public function getTestComposeWithTestDeliveryNumbersAndTruncateMessageFalseData()
    {
        return array(
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages',
                array(
                    'Recipients: 09173149060. Gateway: ENGAGE_SPARK. Message: Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages'
                )
            ),
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph!',
                array('Recipients: 09173149060. Gateway: ENGAGE_SPARK. Message: Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph!')
            ),
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages',
                array(
                    'Recipients: 09173149060. Gateway: ENGAGE_SPARK. Message: Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages'
                )
            ),
            array(
                'Fr AutoDeal: Great news, your listing has been approved and is now live http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages on AutoDeal.com.ph! View your listing at',
                array(
                    'Recipients: 09173149060. Gateway: ENGAGE_SPARK. Message: Fr AutoDeal: Great news, your listing has been approved and is now live http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages on AutoDeal.com.ph! View your listing at'
                )
            )
        );
    }  

    /**
     * @covers Yan/Bundle/SenderSmsBundle/Composer/SmsComposer::splitSms
     * @dataProvider getTestSplitSmsData
     */
    public function testSplitSms($value, $expected)
    {
        $stub = $this->getStubForTest();

        $actual = $stub->splitSms($value);
        
        $this->assertTrue(is_array($actual));
        $this->assertTrue($actual == $expected);
    }

    /**
     * @covers Yan/Bundle/SenderSmsBundle/Composer/SmsComposer::constructSmses
     * @dataProvider getTestConstructSmsesData
     */
    public function testConstructSmses($value, $expected)
    {
        $sms = new Sms();
        $sms->setFrom('AUTODEAL');
        $sms->addRecipient('09173149060');
        $sms->setContent($value);

        $stub = $this->getStubForTest();
        $actual = $stub->constructSmses($sms);
        
        foreach ($actual as $key => $iSms) {
            $this->assertEquals($sms->getFrom(), $iSms->getFrom());
            $this->assertTrue($sms->getRecipients() == $iSms->getRecipients());
            $this->assertEquals($expected[$key], $iSms->getContent());
        }
    }

    /**
     * @covers Yan/Bundle/SenderSmsBundle/Composer/SmsComposer::compose
     * @dataProvider getTestComposeWithTestDeliveryNumbersAndTruncateMessageFalseData
     */
    public function testComposeWithTestDeliveryNumbersData($value, $expected)
    {
        $sms = new Sms();
        $sms->setFrom('AUTODEAL');
        $sms->addRecipient('09173149060');
        $sms->setContent($value);

        $stub = $this->getStubForTest();
        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $gatewayConfigurationMock->expects($this->any())
            ->method('getTestDeliveryNumbers')
            ->will($this->returnValue(array('09173149060')));

        $gatewayConfigurationMock->expects($this->any())
            ->method('getApiName')
            ->will($this->returnValue('ENGAGE_SPARK'));

        $stub->expects($this->any())
             ->method('formatRecipientsForSending')
             ->will($this->returnValue('09173149060'));

        $actual = $stub->compose($sms, $gatewayConfigurationMock);
        
        foreach ($actual as $key => $iSms) {
            $this->assertEquals($sms->getFrom(), $iSms->getFrom());
            $this->assertTrue($sms->getRecipients() == $iSms->getRecipients());
            $this->assertEquals($expected[$key], $iSms->getContent());
        }
    }

    /**
     * @covers Yan/Bundle/SenderSmsBundle/Composer/SmsComposer::compose
     * @dataProvider getTestComposeWithTestDeliveryNumbersAndTruncateMessageTrueData
     */
    public function testComposeWithTestDeliveryNumbersAndTruncateMessageTrueData($value, $expected)
    {
        $sms = new Sms();
        $sms->setFrom('AUTODEAL');
        $sms->addRecipient('09173149060');
        $sms->setContent($value);

        $stub = $this->getStubForTest();
        $gatewayConfigurationMock = $this->getGatewayConfigurationMock();
        $gatewayConfigurationMock->expects($this->any())
            ->method('getTestDeliveryNumbers')
            ->will($this->returnValue(array('09173149060')));

        $gatewayConfigurationMock->expects($this->any())
            ->method('getApiName')
            ->will($this->returnValue('ENGAGE_SPARK'));
            
        $gatewayConfigurationMock->expects($this->any())
            ->method('isTruncateSms')
            ->will($this->returnValue(true));

        $stub->expects($this->any())
             ->method('formatRecipientsForSending')
             ->will($this->returnValue('09173149060'));

        $actual = $stub->compose($sms, $gatewayConfigurationMock);
        
        foreach ($actual as $key => $iSms) {
            $this->assertEquals($sms->getFrom(), $iSms->getFrom());
            $this->assertTrue($sms->getRecipients() == $iSms->getRecipients());
            $this->assertEquals($expected[$key], $iSms->getContent());
        }
    }
}
