<?php

namespace Yan\Bundle\SmsSenderBundle\Tests\Unit\DependencyInjection;

use Yan\Bundle\SmsSenderBundle\DependencyInjection\SmsSenderExtension;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;

class SmsSenderExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $sut;
    private $container;
    private $root;
    
    protected function setUp()
    {
        $this->sut = new SmsSenderExtension();
        $this->container = new ContainerBuilder();
        $this->root = 'yan_sms_sender';
    }

    public function getConfigValuesThatThrowsException()
    {
        return array(
            array(
                array(),
                'The child node "default_sender" at path "yan_sms_sender" must be configured.', 
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            ),
            array(
                array('not_semaphore_sms'), 
                'Invalid type for path "yan_sms_sender". Expected array, but got string',
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
            array(
                array($this->root), 
                'The child node "default_sender" at path "yan_sms_sender" must be configured.',
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
            array(
                array($this->root => array(
                    'default_sender' => 'ME', 
                    'senders' => array()
                )), 
                'The path "yan_sms_sender.senders" should have at least 1 element(s) defined.',
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
            array(
                array($this->root => array(
                    'default_sender' => 'ME', 
                    'senders' => array(
                        'SENDER1' => array()
                    )
                )), 
                'The value for default_sender must be a part of senders list.',
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
            array(
                array($this->root => array(
                    'default_sender' => 'ME', 
                    'senders' => array(
                        'SENDER1' => array('notregisteredkey' => 'value')
                    )
                )), 
                'Unrecognized option "notregisteredkey" under "yan_sms_sender.senders.SENDER1"',
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            )
        );
    }    

    /**
     * @covers Yan/Bundle/SmsSenderBundle/DependencyInjection/SmsSenderBundle::load
     * @dataProvider getConfigValuesThatThrowsException
     */
    public function testThrowExceptionWhenConfigIsInvalid($array, $exceptionMessage, $exception)
    {
        $this->setExpectedException($exception, $exceptionMessage);
        $this->sut->load($array, $this->container);
    }

    /**
     * @covers Yan/Bundle/SemaphoreSmsBundle/DependencyInjection/SemaphoreSmsExtension::load
     */
    public function testDefaultsValues()
    {
        $configs = array(
            $this->root => array(
                'default_sender' => 'SENDER',
                'senders' => array(
                    'SENDER' => array(
                        'api_key' => 'APIKEY'
                    )
                )
            )
        );

        $this->sut->load($configs, $this->container);

        $this->assertTrue($this->container->hasParameter($this->root.".enable_delivery"));
        $this->assertTrue($this->container->hasParameter($this->root.".default_sender"));
        
        $this->assertTrue($this->container->getParameter($this->root.".enable_delivery"));
        $this->assertEquals($configs[$this->root]['default_sender'], $this->container->getParameter($this->root.".default_sender"));
    }

    /**
     * @covers Yan/Bundle/SemaphoreSmsBundle/DependencyInjection/SemaphoreSmsExtension::load
     */
    public function testSenderValues()
    {
        $configs = array(
            $this->root => array(
                'default_sender' => 'sender1',
                'senders' => array(
                    'sender1' => array(
                        'api_key' => 'APIKEY',
                    )
                )
            )
        );

        $this->sut->load($configs, $this->container);

        $this->assertTrue($this->container->hasParameter($this->root.".enable_delivery"));
        $this->assertTrue($this->container->hasParameter($this->root.".default_sender"));
        
        $this->assertTrue($this->container->getParameter($this->root.".enable_delivery"));
        $this->assertEquals($configs[$this->root]['default_sender'], $this->container->getParameter($this->root.".default_sender"));

        $this->assertEquals($configs[$this->root]['senders'], $this->container->getParameter($this->root.".senders"));
        $this->assertEquals($configs[$this->root]['senders']['sender1'], $this->container->getParameter($this->root.".senders.sender1"));
    }
}
