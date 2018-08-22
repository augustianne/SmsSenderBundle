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
 * @version dated: August 7, 2018
 */
class YanSmsSenderExtension extends Extension
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
        $container->setParameter('yan_sms_sender.gateways', $config['gateways']);

        foreach ($config['gateways'] as $key => $sender) {
            $container->setParameter('yan_sms_sender.gateways.'.$key, $config['gateways'][$key]);            
        }

        if (isset($config['default_gateway_id'])) {
            if (!array_key_exists($config['default_gateway_id'], $config['gateways'])) {
                throw new InvalidConfigurationException('The value for default_gateway_id must be a part of gateways list.');
            }

            $container->setParameter('yan_sms_sender.default_gateway_id', $config['default_gateway_id']);
        }

        if (!is_null($config['backup_gateway_id'])) {
            
            if (!array_key_exists($config['backup_gateway_id'], $config['gateways'])) {
                throw new InvalidConfigurationException('The value for backup_gateway_id must be a part of gateways list.');
            }
        }

        $container->setParameter('yan_sms_sender.backup_gateway_id', $config['backup_gateway_id']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
