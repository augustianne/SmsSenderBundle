<?php

/*
 * This file is part of SmsSenderBundle.
 *
 * Yan Barreta <augustianne.barreta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yan\Bundle\SmsSenderBundle\Tests\Unit\Sender;

use Yan\Bundle\SmsSenderBundle\Sender\SmsSender;

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
class SmsSenderTest extends \PHPUnit_Framework_TestCase
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

    public function getSmsGatewayProviderMock()
    {
        $configurationMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Gateway\SmsGatewayProvider')
            ->disableOriginalConstructor()
            ->getMock();

        return $configurationMock;
    }

    public function getSmsMock()
    {
        $smsMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Composer\Sms')
            ->disableOriginalConstructor()
            ->getMock();

        return $smsMock;
    }

    public function getEngageSparkGatewayMock()
    {
        $gatewayMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Gateway\EngageSparkSmsGateway')
            ->disableOriginalConstructor()
            ->getMock();

        return $gatewayMock;
    }

    public function getSemaphoreRegularSmsGatewayMock()
    {
        $gatewayMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Gateway\SemaphoreRegularSmsGateway')
            ->disableOriginalConstructor()
            ->getMock();

        return $gatewayMock;
    }

    public function getSemaphorePrioritySmsGatewayMock()
    {
        $gatewayMock = $this->getMockBuilder('Yan\Bundle\SmsSenderBundle\Gateway\SemaphorePrioritySmsGateway')
            ->disableOriginalConstructor()
            ->getMock();

        return $gatewayMock;
    }

    public function getGatewayMocks()
    {
        $engageSparkSmsGateway = $this->getEngageSparkGatewayMock();
        $engageSparkSmsGateway->expects($this->any())
             ->method('getName')
             ->will($this->returnValue('ENGAGE_SPARK'));

        $semaphorePrioritySmsGateway = $this->getSemaphorePrioritySmsGatewayMock();
        $semaphorePrioritySmsGateway->expects($this->any())
             ->method('getName')
             ->will($this->returnValue('SEMAPHORE_PRIORITY'));

        $semaphoreRegularSmsGateway = $this->getSemaphoreRegularSmsGatewayMock();
        $semaphoreRegularSmsGateway->expects($this->any())
             ->method('getName')
             ->will($this->returnValue('SEMAPHORE_REGULAR'));

        return array(
            'ENGAGE_SPARK' => $engageSparkSmsGateway,
            'SEMAPHORE_PRIORITY' => $semaphorePrioritySmsGateway,
            'SEMAPHORE_REGULAR' => $semaphoreRegularSmsGateway,
        );
    }

    public function getGatewayIds()
    {
        return array(
            array('ENGAGE_SPARK'),
            array('SEMAPHORE_REGULAR'),
            array('SEMAPHORE_PRIORITY')
        );
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::send
     * @dataProvider getGatewayIds
     */
    public function testSendDefaultGatewaySuccess($gatewayId)
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        
        $configurationMock->expects($this->any())
            ->method('isDeliveryEnabled')
            ->will($this->returnValue(true));
        
        $gatewayMocks = $this->getGatewayMocks();
        $defaultGateway = $gatewayMocks[$gatewayId];
        $defaultGateway->expects($this->any())
            ->method('send')
            ->will($this->returnValue(true));

        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->returnValue($defaultGateway));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);
        $this->assertTrue($sut->send($smsMock));
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::send
     */
    public function testSendDefaultGatewayThrowsGatewayNotFoundExceptionNoBackup()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        
        $configurationMock->expects($this->any())
             ->method('isDeliveryEnabled')
             ->will($this->returnValue(true));
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException()));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException()));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);
        $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException');
        $sut->send($smsMock);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::send
     */
    public function testSendDefaultGatewayThrowsDeliveryFailureExceptionNoBackup()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        
        $configurationMock->expects($this->any())
             ->method('isDeliveryEnabled')
             ->will($this->returnValue(true));
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException()));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException()));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);
        $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException');

        $sut->send($smsMock);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::send
     */
    public function testSendDefaultGatewayThrowsGatewayNotFoundExceptionWithBackup()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        $configurationMock->expects($this->any())
             ->method('isDeliveryEnabled')
             ->will($this->returnValue(true));
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException()));

        $gatewayMocks = $this->getGatewayMocks();
        $backupGateway = $gatewayMocks['ENGAGE_SPARK'];
        $backupGateway->expects($this->any())
            ->method('send')
            ->will($this->returnValue(true));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->returnValue($backupGateway));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);
        $this->assertTrue($sut->send($smsMock));
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::send
     */
    public function testSendDefaultGatewayThrowsDeliveryFailureExceptionWithBackup()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        $configurationMock->expects($this->any())
             ->method('isDeliveryEnabled')
             ->will($this->returnValue(true));
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException()));

        $gatewayMocks = $this->getGatewayMocks();
        $backupGateway = $gatewayMocks['ENGAGE_SPARK'];
        $backupGateway->expects($this->any())
            ->method('send')
            ->will($this->returnValue(true));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->returnValue($backupGateway));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);
        $this->assertTrue($sut->send($smsMock));
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::send
     */
    public function testSendDefaultGatewayThrowsDeliveryFailureExceptionBackupThrowsDeliveryFailureException()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        $configurationMock->expects($this->any())
             ->method('isDeliveryEnabled')
             ->will($this->returnValue(true));
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException()));

        $gatewayMocks = $this->getGatewayMocks();
        $backupGateway = $gatewayMocks['ENGAGE_SPARK'];
        $backupGateway->expects($this->any())
            ->method('send')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException()));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->returnValue($backupGateway));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);

        $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException');
        $sut->send($smsMock);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::getAccountBalance
     * @dataProvider getGatewayIds
     */
    public function testGetAccountBalanceDefaultGatewaySuccess($gatewayId)
    {
        $configurationMock = $this->getConfigurationMock();
        
        $gatewayMocks = $this->getGatewayMocks();
        $defaultGateway = $gatewayMocks[$gatewayId];
        $defaultGateway->expects($this->any())
            ->method('getAccountBalance')
            ->will($this->returnValue(2000));

        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->returnValue($defaultGateway));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);
        $this->assertEquals(2000, $sut->getAccountBalance());
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::getAccountBalance
     */
    public function testGetAccountBalanceDefaultGatewayThrowsGatewayNotFoundExceptionNoBackup()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException()));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException()));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);
        $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException');
        $sut->getAccountBalance($smsMock);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::getAccountBalance
     */
    public function testGetAccountBalanceDefaultGatewayThrowsDeliveryFailureExceptionNoBackup()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException()));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException()));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);
        $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException');

        $sut->getAccountBalance($smsMock);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::getAccountBalance
     */
    public function testGetAccountBalanceDefaultGatewayThrowsGatewayNotFoundExceptionWithBackup()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException()));

        $gatewayMocks = $this->getGatewayMocks();
        $backupGateway = $gatewayMocks['ENGAGE_SPARK'];
        $backupGateway->expects($this->any())
            ->method('getAccountBalance')
            ->will($this->returnValue(2000));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->returnValue($backupGateway));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);
        $this->assertEquals(2000, $sut->getAccountBalance($smsMock));
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::getAccountBalance
     */
    public function testGetAccountBalanceDefaultGatewayThrowsDeliveryFailureExceptionWithBackup()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException()));

        $gatewayMocks = $this->getGatewayMocks();
        $backupGateway = $gatewayMocks['ENGAGE_SPARK'];
        $backupGateway->expects($this->any())
            ->method('getAccountBalance')
            ->will($this->returnValue(2000));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->returnValue($backupGateway));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);
        $this->assertEquals(2000, $sut->getAccountBalance($smsMock));
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::getAccountBalance
     */
    public function testGetAccountBalanceDefaultGatewayThrowsDeliveryFailureExceptionBackupThrowsDeliveryFailureException()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException()));

        $gatewayMocks = $this->getGatewayMocks();
        $backupGateway = $gatewayMocks['ENGAGE_SPARK'];
        $backupGateway->expects($this->any())
            ->method('getAccountBalance')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException()));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->returnValue($backupGateway));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);

        $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException');
        $sut->getAccountBalance($smsMock);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::getAccountBalance
     */
    public function testGetAccountBalanceDefaultGatewayThrowsDeliveryFailureExceptionBackupThrowsDeliveryFailureExceptionCatchesSmsSenderException()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException()));

        $gatewayMocks = $this->getGatewayMocks();
        $backupGateway = $gatewayMocks['ENGAGE_SPARK'];
        $backupGateway->expects($this->any())
            ->method('getAccountBalance')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException()));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->returnValue($backupGateway));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);

        $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\SmsSenderException');
        $sut->getAccountBalance($smsMock);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::getAccountBalance
     */
    public function testGetAccountBalanceDefaultGatewayThrowsGatewayNotFoundExceptionBackupThrowsGatewayNotFoundExceptionCatchesSmsSenderException()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException()));

        $gatewayMocks = $this->getGatewayMocks();
        $backupGateway = $gatewayMocks['ENGAGE_SPARK'];
        $backupGateway->expects($this->any())
            ->method('getAccountBalance')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException()));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->returnValue($backupGateway));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);

        $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\SmsSenderException');
        $sut->getAccountBalance($smsMock);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::send
     */
    public function testSendDefaultGatewayThrowsDeliveryFailureExceptionBackupThrowsDeliveryFailureExceptionCatchesSmsSenderException()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        $configurationMock->expects($this->any())
             ->method('isDeliveryEnabled')
             ->will($this->returnValue(true));
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException()));

        $gatewayMocks = $this->getGatewayMocks();
        $backupGateway = $gatewayMocks['ENGAGE_SPARK'];
        $backupGateway->expects($this->any())
            ->method('send')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException()));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->returnValue($backupGateway));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);

        $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\SmsSenderException');
        $sut->send($smsMock);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsSender::send
     */
    public function testSendDefaultGatewayThrowsGatewayNotFoundExceptionBackupThrowsGatewayNotFoundExceptionCatchesSmsSenderException()
    {
        $smsMock = $this->getSmsMock();

        $configurationMock = $this->getConfigurationMock();
        $configurationMock->expects($this->any())
             ->method('isDeliveryEnabled')
             ->will($this->returnValue(true));
        
        $smsGatewayProvider = $this->getSmsGatewayProviderMock();
        $smsGatewayProvider->expects($this->any())
            ->method('getDefaultGateway')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException()));

        $gatewayMocks = $this->getGatewayMocks();
        $backupGateway = $gatewayMocks['ENGAGE_SPARK'];
        $backupGateway->expects($this->any())
            ->method('send')
            ->will($this->throwException(new \Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException()));

        $smsGatewayProvider->expects($this->any())
            ->method('getBackupGateway')
            ->will($this->returnValue($backupGateway));

        $sut = new SmsSender($configurationMock, $smsGatewayProvider);

        $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\SmsSenderException');
        $sut->send($smsMock);
    }
}
