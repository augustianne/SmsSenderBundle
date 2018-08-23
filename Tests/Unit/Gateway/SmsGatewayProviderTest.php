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

use Yan\Bundle\SmsSenderBundle\Gateway\SmsGatewayProvider;

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
class SmsGatewayProviderTest extends \PHPUnit_Framework_TestCase
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
            'ENGAGE_SPARK_ID' => $engageSparkSmsGateway,
            'SEMAPHORE_PRIORITY_ID' => $semaphorePrioritySmsGateway,
            'SEMAPHORE_REGULAR_ID' => $semaphoreRegularSmsGateway,
        );
    }

    public function getGatewayIds()
    {
        return array(
            array('ENGAGE_SPARK_ID', 'ENGAGE_SPARK', false),
            array('ENGAGE_SPARKS', 'ENGAGE_SPARK', true),
            array('SEMAPHORE_REGULAR_ID', 'SEMAPHORE_REGULAR', false),
            array(null, 'ENGAGE_SPARK', true),
            array('SEMAPHORE_PRIORITY_ID', 'SEMAPHORE_PRIORITY', false),
            array('SEMAPHORE_PRIORITIES', 'SEMAPHORE_PRIORITY', true),
        );
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsGatewayProvider::getGatewayById
     * @dataProvider getGatewayIds
     */
    public function testGetGatewayById($gatewayId, $apiName, $throwsException)
    {
        $gatewayMocks = $this->getGatewayMocks();

        $configurationMock = $this->getConfigurationMock();

        $i = 0;
        foreach ($gatewayMocks as $iGatewayId => $gateway) {
            $configurationMock->expects($this->at($i))
                ->method('getGatewayIdByApiName')
                ->with($gateway->getName())
                ->will($this->returnValue($iGatewayId));

            $i++;
        }

        $sut = new SmsGatewayProvider($configurationMock, $gatewayMocks);

        if ($throwsException) {
            $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException');
        }

        $actualGateway = $sut->getGatewayById($gatewayId);
        $expectedGateway = $gatewayMocks[$gatewayId];
        
        $this->assertEquals($expectedGateway, $actualGateway);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsGatewayProvider::getDefaultGateway
     * @dataProvider getGatewayIds
     */
    public function testGetDefaultGateway($gatewayId, $apiName, $throwsException)
    {
        $gatewayMocks = $this->getGatewayMocks();

        $configurationMock = $this->getConfigurationMock();
        $configurationMock->expects($this->any())
            ->method('getDefaultGatewayId')
            ->will($this->returnValue($gatewayId));

        $i = 0;
        foreach ($gatewayMocks as $iGatewayId => $gateway) {
            $configurationMock->expects($this->at($i))
                ->method('getGatewayIdByApiName')
                ->with($gateway->getName())
                ->will($this->returnValue($iGatewayId));

            $i++;
        }

        $sut = new SmsGatewayProvider($configurationMock, $gatewayMocks);

        if ($throwsException) {
            $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException');
        }

        $actualGateway = $sut->getDefaultGateway();
        $expectedGateway = $gatewayMocks[$gatewayId];

        $this->assertEquals($expectedGateway, $actualGateway);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsGatewayProvider::getBackupGateway
     * @dataProvider getGatewayIds
     */
    public function testGetBackupGateway($gatewayId, $apiName, $throwsException)
    {
        $gatewayMocks = $this->getGatewayMocks();

        $configurationMock = $this->getConfigurationMock();
        $configurationMock->expects($this->any())
            ->method('getBackupGatewayId')
            ->will($this->returnValue($gatewayId));

        $i = 0;
        foreach ($gatewayMocks as $iGatewayId => $gateway) {
            $configurationMock->expects($this->at($i))
                ->method('getGatewayIdByApiName')
                ->with($gateway->getName())
                ->will($this->returnValue($iGatewayId));

            $i++;
        }
        
        $sut = new SmsGatewayProvider($configurationMock, $gatewayMocks);

        if ($throwsException) {
            $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException');
        }

        $actualGateway = $sut->getBackupGateway();
        $expectedGateway = $gatewayMocks[$gatewayId];

        $this->assertEquals($expectedGateway, $actualGateway);
    }
}
