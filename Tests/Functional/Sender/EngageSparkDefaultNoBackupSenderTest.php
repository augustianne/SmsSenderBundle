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
            // array(array('09173149060', '09173149060', '09173222385'), 'Test message 2.'),
            // array(array('09173149060', '09173222385'), 'Test message 3.'),
            // array(array('09173149060', '09173149060'), 'Test message 4.')
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

        // $sent = $smsSender->send($sms);
        // $this->assertTrue($sent);
        $this->markTestSkipped();
    }
}
