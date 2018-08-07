<?php

namespace Yan\Bundle\SmsSenderBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Bundle Extension
 *
 * @author  Yan Barreta
 * @version dated: April 30, 2015 3:55:29 PM
 */
class SmsSenderExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $container->setParameter('yan_sms_sender', $config);
        
        $container->setParameter('yan_sms_sender.enable_delivery', $config['enable_delivery']);
        $container->setParameter('yan_sms_sender.senders', $config['senders']);

        foreach ($config['senders'] as $key => $sender) {
            $container->setParameter('yan_sms_sender.senders.'.$key, $config['senders'][$key]);            
        }

        if (isset($config['default_sender'])) {
            if (!array_key_exists($config['default_sender'], $config['senders'])) {
                throw new InvalidConfigurationException('The value for default_sender must be a part of senders list.');
            }

            $container->setParameter('yan_sms_sender.default_sender', $config['default_sender']);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
