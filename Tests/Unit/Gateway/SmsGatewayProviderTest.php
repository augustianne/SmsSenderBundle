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
            'ENGAGE_SPARK' => $engageSparkSmsGateway,
            'SEMAPHORE_PRIORITY' => $semaphorePrioritySmsGateway,
            'SEMAPHORE_REGULAR' => $semaphoreRegularSmsGateway,
        );
    }

    public function getGatewayIds()
    {
        return array(
            array('ENGAGE_SPARK', false),
            array('ENGAGE_SPARKS', true),
            array('SEMAPHORE_REGULAR', false),
            array(null, true),
            array('SEMAPHORE_PRIORITY', false),
            array('SEMAPHORE_PRIORITIES', true),
        );
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsGatewayProvider::getUrl
     * @dataProvider getGatewayIds
     */
    public function testGetGatewayById($gatewayId, $throwsException)
    {
        $configurationMock = $this->getConfigurationMock();
        $gatewayMocks = $this->getGatewayMocks();

        $sut = new SmsGatewayProvider($configurationMock, $gatewayMocks);

        if ($throwsException) {
            $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException');
        }

        $actualGateway = $sut->getGatewayById($gatewayId);
        $expectedGateway = $gatewayMocks[$gatewayId];

        $this->assertEquals($expectedGateway, $actualGateway);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsGatewayProvider::getUrl
     * @dataProvider getGatewayIds
     */
    public function testGetDefaultGateway($gatewayId, $throwsException)
    {
        $configurationMock = $this->getConfigurationMock();
        $configurationMock->expects($this->any())
             ->method('getDefaultGatewayId')
             ->will($this->returnValue($gatewayId));

        $gatewayMocks = $this->getGatewayMocks();

        $sut = new SmsGatewayProvider($configurationMock, $gatewayMocks);

        if ($throwsException) {
            $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException');
        }

        $actualGateway = $sut->getGatewayById($gatewayId);
        $expectedGateway = $gatewayMocks[$gatewayId];

        $this->assertEquals($expectedGateway, $actualGateway);
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SmsGatewayProvider::getUrl
     * @dataProvider getGatewayIds
     */
    public function testGetBackupGateway($gatewayId, $throwsException)
    {
        $configurationMock = $this->getConfigurationMock();
        $configurationMock->expects($this->any())
             ->method('getBackupGatewayId')
             ->will($this->returnValue($gatewayId));
             
        $gatewayMocks = $this->getGatewayMocks();

        $sut = new SmsGatewayProvider($configurationMock, $gatewayMocks);

        if ($throwsException) {
            $this->setExpectedException('\Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException');
        }

        $actualGateway = $sut->getGatewayById($gatewayId);
        $expectedGateway = $gatewayMocks[$gatewayId];

        $this->assertEquals($expectedGateway, $actualGateway);
    }
}
