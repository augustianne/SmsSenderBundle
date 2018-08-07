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
 * @version dated: April 30, 2015 3:55:29 PM
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
                ->ifTrue(function ($v) { return is_array($v) && !array_key_exists('senders', $v) && !array_key_exists('sender', $v); })
                ->then(function ($v) {
                    $sender = array();
                    foreach ($v as $key => $value) {
                        if ('default_sender' == $key) {
                            continue;
                        }
                        $sender[$key] = $v[$key];
                        unset($v[$key]);
                    }
                    $v['default_sender'] = isset($v['default_sender']) ? (string) $v['default_sender'] : 'default';
                    $v['senders'] = array($v['default_sender'] => $sender);

                    return $v;
                })
            ->end()
            ->children()
                ->booleanNode('enable_delivery')->defaultTrue()->end()
                ->scalarNode('default_sender')->isRequired()->end()
                ->append($this->getSendersNode())
            ->end();

        return $treeBuilder;
    }

    private function getSendersNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('senders');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
                ->prototype('array')
            ->children()
                ->scalarNode('api')->end()
                ->scalarNode('api_key')->end()
                ->scalarNode('sender_name')->end()
                ->arrayNode('delivery_numbers')
                    ->performNoDeepMerging()
                ->end()
            ->end()
        ;

        return $node;
    }
}
