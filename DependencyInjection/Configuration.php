<?php

/*
 * This file is part of SmsSenderBundle.
 *
 * Yan Barreta <augustianne.barreta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yan\Bundle\SmsSenderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Bundle configuration
 *
 * @author  Yan Barreta
 * @version dated: August 7, 2018
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('yan_sms_sender');

        $rootNode
            ->beforeNormalization()
                ->ifTrue(function ($v) { return is_array($v) && !array_key_exists('gateways', $v); })
                ->then(function ($v) {
                    $sender = array();
                    foreach ($v as $key => $value) {
                        if ('default_gateway_id' == $key) {
                            continue;
                        }
                        $sender[$key] = $v[$key];
                        unset($v[$key]);
                    }
                    $v['default_gateway_id'] = isset($v['default_gateway_id']) ? (string) $v['default_gateway_id'] : 'default';
                    $v['gateways'] = array($v['default_gateway_id'] => $sender);

                    return $v;
                })
            ->end()
            ->children()
                ->booleanNode('enable_delivery')->defaultTrue()->end()
                ->scalarNode('default_gateway_id')->isRequired()->end()
                ->scalarNode('backup_gateway_id')->defaultNull()->end()
                ->append($this->getSendersNode())
            ->end();

        return $treeBuilder;
    }

    private function getSendersNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('gateways');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
                ->prototype('array')
            ->children()
                ->scalarNode('api_name')
                    ->validate()
                        ->ifNotInArray(array('ENGAGE_SPARK', 'SEMAPHORE_PRIORITY', 'SEMAPHORE_REGULAR'))
                        ->thenInvalid('The %s sms sender is not supported')
                    ->end()
                ->end()
                ->scalarNode('api_key')->end()
                ->scalarNode('sender_name')->end()
                ->booleanNode('truncate_sms')->defaultFalse()->end()
                ->scalarNode('default_country_code')->defaultValue('63')->end()
                ->arrayNode('test_delivery_numbers')
                    ->performNoDeepMerging()
                ->end()
                ->scalarNode('organization_id')->end()
                ->scalarNode('recipient_type')
                    ->validate()
                        ->ifNotInArray(array('mobile_number', 'contact_id'))
                        ->thenInvalid('The %s recipient type is not supported')
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
