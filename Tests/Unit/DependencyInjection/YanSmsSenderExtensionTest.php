<?php

namespace Yan\Bundle\SmsSenderBundle\Tests\Unit\DependencyInjection;

use Yan\Bundle\SmsSenderBundle\DependencyInjection\YanSmsSenderExtension;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;

class YanSmsSenderExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $sut;
    private $container;
    private $root;
    
    protected function setUp()
    {
        $this->sut = new YanSmsSenderExtension();
        $this->container = new ContainerBuilder();
        $this->root = 'yan_sms_sender';
    }

    public function getConfigValuesThatThrowsException()
    {
        return array(
            array(
                array(),
                'The child node "default_gateway_id" at path "yan_sms_sender" must be configured.',
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            ),
            array(
                array('not_semaphore_sms'), 
                'Invalid type for path "yan_sms_sender". Expected array, but got string',
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
            array(
                array($this->root), 
                'The child node "default_gateway_id" at path "yan_sms_sender" must be configured.',
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
            array(
                array($this->root => array(
                    'default_gateway_id' => 'ME', 
                    'gateways' => array()
                )), 
                'The path "yan_sms_sender.gateways" should have at least 1 element(s) defined.',
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
            array(
                array($this->root => array(
                    'default_gateway_id' => 'ME', 
                    'gateways' => array(
                        'SENDER1' => array(
                            'api_name' => 'ENGAGE_SPARK',
                            'api_key' => 'APIKEY',
                            'sender_name' => 'test'
                        )
                    )
                )), 
                'The value for default_gateway_id must be a part of gateways list.',
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
            array(
                array($this->root => array(
                    'default_gateway_id' => 'ME', 
                    'gateways' => array(
                        'SENDER1' => array('notregisteredkey' => 'value')
                    )
                )), 
                'Unrecognized option "notregisteredkey" under "yan_sms_sender.gateways.SENDER1"',
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
            array(
                array($this->root => array(
                    'default_gateway_id' => 'SENDER1', 
                    'gateways' => array(
                        'SENDER1' => array('api_name' => 'NOT_SUPPORTED_API')
                    )
                )), 
                'The "NOT_SUPPORTED_API" sms sender is not supported',
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
            array(
                array($this->root => array(
                    'default_gateway_id' => 'SENDER1', 
                    'gateways' => array(
                        'SENDER1' => array(
                            'api_name' => 'ENGAGE_SPARK',
                            'api_key' => 'APIKEY',
                            'sender_name' => 'test',
                            'recipient_type' => 'NOT_SUPPORTED'
                        )
                    )
                )), 
                'The "NOT_SUPPORTED" recipient type is not supported',
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
                'default_gateway_id' => 'SENDER',
                'gateways' => array(
                    'SENDER' => array(
                        'api_key' => 'APIKEY',
                        'api_name' => 'ENGAGE_SPARK',
                        'truncate_sms' => false,
                        'sender_name' => 'SENDER_NAME',
                        'default_country_code' => '63',
                        'test_delivery_numbers' => array()
                    )
                )
            )
        );

        $this->sut->load($configs, $this->container);

        $this->assertTrue($this->container->hasParameter($this->root.".enable_delivery"));
        $this->assertTrue($this->container->hasParameter($this->root.".default_gateway_id"));

        $this->assertTrue($this->container->getParameter($this->root.".enable_delivery"));

        $this->assertEquals($configs[$this->root]['gateways']['SENDER'], $this->container->getParameter($this->root.".gateways.SENDER"));
        $this->assertEquals($configs[$this->root]['default_gateway_id'], $this->container->getParameter($this->root.".default_gateway_id"));
    }

    /**
     * @covers Yan/Bundle/SemaphoreSmsBundle/DependencyInjection/SemaphoreSmsExtension::load
     */
    public function testSenderValues()
    {
        $configs = array(
            $this->root => array(
                'default_gateway_id' => 'sender1',
                'gateways' => array(
                    'sender1' => array(
                        'api_key' => 'APIKEY',
                        'truncate_sms' => false,
                        'sender_name' => 'SENDER_NAME',
                        'default_country_code' => '63',
                        'test_delivery_numbers' => array()
                    )
                )
            )
        );

        $this->sut->load($configs, $this->container);

        $this->assertTrue($this->container->hasParameter($this->root.".enable_delivery"));
        $this->assertTrue($this->container->hasParameter($this->root.".default_gateway_id"));
        
        $this->assertTrue($this->container->getParameter($this->root.".enable_delivery"));
        $this->assertEquals($configs[$this->root]['default_gateway_id'], $this->container->getParameter($this->root.".default_gateway_id"));

        $this->assertEquals($configs[$this->root]['gateways'], $this->container->getParameter($this->root.".gateways"));
        $this->assertEquals($configs[$this->root]['gateways']['sender1'], $this->container->getParameter($this->root.".gateways.sender1"));
    }
}
