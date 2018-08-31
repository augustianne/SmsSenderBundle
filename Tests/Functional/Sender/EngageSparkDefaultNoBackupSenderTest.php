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
class EngageSparkDefaultNoBackupSenderTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $kernel;

    public function setUp()
    {
        $this->kernel = new AppKernel('engage_spark_default_no_backup', true);
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
            array(array('09279590804'), 'Test message 1.'),
            array(array('09173149060', '09173149060', '09279590804'), 'Test message 2.'),
            array(array('09173149060', '09279590804'), 'Test message 3.'),
            array(array('09173149060', '09173149060'), 'Test message 4.')
        );
    }

    public function getTestSendLongMessages()
    {
        return array(
            array(
                array('09173149060'), 'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages',
            ),
            array(
                array('09173149060'), 'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph!',
            ),
            array(
                array('09173149060'), 'Fr AutoDeal: Great news, your listing has been approved and is now live on AutoDeal.com.ph! View your listing at http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages',
            ),
            array(
                array('09173149060'), 'Fr AutoDeal: Great news, your listing has been approved and is now live http://www.staging3.autotaging3.autodeal.com.ph/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages/app_dev.php/account/messages on AutoDeal.com.ph! View your listing at',
            )
        );
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Sender/SmsSender::send
     * @dataProvider getMessageDefaultSenderData
     */
    public function testSend($numbers, $content)
    {
        $sms = new Sms();
        $sms->setContent('Engage Spark: '.$content);

        foreach ($numbers as $number) {
            $sms->addRecipient($number);
        }

        $smsSender = $this->container->get('yan_sms_sender.sender.sms');

        // $this->assertTrue($smsSender->send($sms));
        $this->markTestSkipped();
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Sender/SmsSender::send
     * @dataProvider getTestSendLongMessages
     */
    public function testSendLongMessagesWithoutTruncation($numbers, $content)
    {
        $sms = new Sms();
        $sms->setContent($content);

        foreach ($numbers as $number) {
            $sms->addRecipient($number);
        }

        $smsSender = $this->container->get('yan_sms_sender.sender.sms');

        $this->assertTrue($smsSender->send($sms));
        // $this->markTestSkipped();
    }

    /**
     * @covers Yan/Bundle/SmsSenderBundle/Sender/SmsSender::getAccountBalance
     */
    public function testGetAccountBalance()
    {
        $smsSender = $this->container->get('yan_sms_sender.sender.sms');

        $this->assertTrue(is_numeric($smsSender->getAccountBalance()));
    }
}
