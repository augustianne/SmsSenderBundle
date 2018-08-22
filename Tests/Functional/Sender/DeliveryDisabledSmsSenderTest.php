<?php

/*
 * This file is part of SmsSenderBundle.
 *
 * Yan Barreta <augustianne.barreta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yan\Bundle\SmsSenderBundle\Tests\Functional\Sender;

use Yan\Bundle\SmsSenderBundle\Composer\Sms;
use Yan\Bundle\SmsSenderBundle\Tests\Fixtures\AppKernel;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Unit test for SingleSmsSender
 *
 * @author  Yan Barreta
 * @version dated: April 30, 2015 3:55:29 PM
 */
class DisabledDeliverySmsSenderTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $kernel;

    public function setUp()
    {
        $this->kernel = new AppKernel('delivery_disabled', true);
        $this->kernel->boot();
        
        $this->container = $this->kernel->getContainer();

        $fixturesDir = __DIR__.'/../../Fixtures';
        $this->fs = new FileSystem();
        $this->fs->remove(array($fixturesDir.'/cache', $fixturesDir.'/logs'));
    }

    public function getMessageDefaultSenderData()
    {
        return array(
            array(array('09173149060'), 'Test message 1.'),
            array(array('09173149060', '09173149060', '09173222385'), 'Test message 2.'),
            array(array('09173149060', '09173222385'), 'Test message 3.'),
            array(array('09173149060', '09173149060'), 'Test message 4.')
        );
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Sender/SmsSender::send
     * @dataProvider getMessageDefaultSenderData
     */
    public function testSendDeliveryDisabled($numbers, $content)
    {
        $sms = new Sms();
        $sms->setContent('Regular: '.$content);

        foreach ($numbers as $number) {
            $sms->addRecipient($number);
        }

        $smsSender = $this->container->get('yan_sms_sender.sender.sms');

        $sent = $smsSender->send($sms);
        $this->assertTrue(is_null($sent), 'sending was not disabled');
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/EngageSparkSmsGateway::send
     * @dataProvider getMessageDefaultSenderData
     */
    public function testSendDeliveryDisabledEngageSparkGateway($numbers, $content)
    {
        $sms = new Sms();
        $sms->setContent('Regular: '.$content);

        foreach ($numbers as $number) {
            $sms->addRecipient($number);
        }

        $smsSender = $this->container->get('yan_sms_sender.gateway.sms.engage_spark');

        $sent = $smsSender->send($sms);
        $this->assertTrue(is_null($sent), 'sending was not disabled');
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/Semaphore::send
     * @dataProvider getMessageDefaultSenderData
     */
    public function testSendDeliveryDisabledSemaphoreGateway($numbers, $content)
    {
        $sms = new Sms();
        $sms->setContent('Regular: '.$content);

        foreach ($numbers as $number) {
            $sms->addRecipient($number);
        }

        $smsSender = $this->container->get('yan_sms_sender.gateway.sms.semaphore');

        $sent = $smsSender->send($sms);
        $this->assertTrue(is_null($sent), 'sending was not disabled');
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SemaphoreRegular::send
     * @dataProvider getMessageDefaultSenderData
     */
    public function testSendDeliveryDisabledSemaphoreRegularGateway($numbers, $content)
    {
        $sms = new Sms();
        $sms->setContent('Regular: '.$content);

        foreach ($numbers as $number) {
            $sms->addRecipient($number);
        }

        $smsSender = $this->container->get('yan_sms_sender.gateway.sms.semaphore.regular');

        $sent = $smsSender->send($sms);
        $this->assertTrue(is_null($sent), 'sending was not disabled');
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Gateway/SemaphorePriority::send
     * @dataProvider getMessageDefaultSenderData
     */
    public function testSendDeliveryDisabledSemaphorePriorityGateway($numbers, $content)
    {
        $sms = new Sms();
        $sms->setContent('Regular: '.$content);

        foreach ($numbers as $number) {
            $sms->addRecipient($number);
        }

        $smsSender = $this->container->get('yan_sms_sender.gateway.sms.semaphore.priority');

        $sent = $smsSender->send($sms);
        $this->assertTrue(is_null($sent), 'sending was not disabled');
    }
}
